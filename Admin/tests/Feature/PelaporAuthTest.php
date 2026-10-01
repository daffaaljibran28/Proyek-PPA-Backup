<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class PelaporAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelapor_can_register_into_the_existing_user_table(): void
    {
        $response = $this->post('/pelapor/register', [
            'name' => 'Rani Pelapor',
            'nik' => '3201010101010001',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Bandung',
            'email' => 'rani@example.com',
            'no_hp' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/pelapor/login')->assertSessionHas('success');
        $this->assertDatabaseHas('user', [
            'email' => 'rani@example.com',
            'role' => 'pengguna',
        ]);
    }

    public function test_registration_rejects_names_with_numbers_or_symbols(): void
    {
        $response = $this->post('/pelapor/register', [
            'name' => 'Rani123!',
            'nik' => '3201010101010001',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Bandung',
            'email' => 'rani@example.com',
            'no_hp' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['name' => 'Nama lengkap hanya boleh berisi huruf dan spasi.']);
        $this->assertDatabaseMissing('user', ['email' => 'rani@example.com']);
    }

    public function test_pelapor_login_rejects_an_admin_account(): void
    {
        User::create([
            'name' => 'Admin SAPA',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);

        $response = $this->post('/pelapor/login', [
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect()->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_session_cannot_access_the_pelapor_portal(): void
    {
        $admin = User::create([
            'name' => 'Admin SAPA',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->get('/pelapor')->assertForbidden();
    }

    public function test_pelapor_session_cannot_access_admin_routes(): void
    {
        $user = $this->createPelapor();

        $this->actingAs($user)->get('/')->assertForbidden();
        $this->actingAs($user)->get('/laporan')->assertForbidden();
        $this->actingAs($user)->get('/pengguna')->assertForbidden();
    }

    public function test_pelapor_can_log_in_with_a_session(): void
    {
        $user = $this->createPelapor();

        $response = $this->post('/pelapor/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/pelapor');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_pelapor_can_create_a_report_owned_by_their_account(): void
    {
        $this->useTemporaryPublicStorage();
        $user = $this->createPelapor();

        $response = $this->actingAs($user)->post('/pelapor/pengaduan', [
            'kategori' => 'Kekerasan terhadap Anak',
            'sebagai' => 'Keluarga Korban',
            'lokasi_kejadian' => 'Bandung',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('Kronologi kejadian untuk pengujian laporan. ', 3),
            'foto' => [UploadedFile::fake()->image('bukti.jpg')],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('laporan', [
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        $laporan = Laporan::where('id_pengguna', $user->id)->firstOrFail();
        $references = explode(', ', $laporan->bukti_lampiran);
        $this->assertCount(1, $references);
        $this->assertStringStartsWith('laporan/' . $laporan->id_laporan . '/', $references[0]);
        Storage::disk('local')->assertExists($references[0]);
        $this->actingAs($user)->get('/pelapor/pengaduan/' . $laporan->id_laporan . '/bukti/0')->assertOk();

        $otherUser = $this->createPelapor('bukan-pemilik@example.com');
        $this->actingAs($otherUser)->get('/pelapor/pengaduan/' . $laporan->id_laporan . '/bukti/0')->assertNotFound();

        $admin = User::create([
            'name' => 'Admin Bukti',
            'email' => 'admin-bukti@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin)->get('/laporan/' . $laporan->id_laporan . '/bukti/0')->assertOk();
    }

    public function test_pelapor_cannot_view_another_users_report(): void
    {
        $user = $this->createPelapor('pemilik@example.com');
        $otherUser = $this->createPelapor('lain@example.com');
        $laporan = Laporan::create([
            'id_pengguna' => $otherUser->id,
            'nama_pelapor' => $otherUser->name,
            'kategori' => 'Kekerasan terhadap Perempuan',
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        $this->actingAs($user)
            ->get('/pelapor/pengaduan/' . $laporan->id_laporan)
            ->assertNotFound();
    }

    public function test_report_status_updated_by_staff_is_visible_to_its_owner(): void
    {
        $user = $this->createPelapor();
        $laporan = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Sedang Diproses',
        ]);

        $this->actingAs($user)
            ->get('/pelapor/pengaduan/' . $laporan->id_laporan)
            ->assertOk()
            ->assertSee('Sedang Diproses');
    }

    public function test_main_pelapor_pages_render_for_an_authenticated_user(): void
    {
        $user = $this->createPelapor();

        foreach (['/pelapor', '/pelapor/pengaduan', '/pelapor/pengaduan/buat', '/pelapor/informasi-edukasi', '/pelapor/emergency-call', '/pelapor/profil'] as $path) {
            $this->actingAs($user)->get($path)->assertOk();
        }
    }

    public function test_pelapor_cannot_submit_another_report_while_the_latest_is_active(): void
    {
        $user = $this->createPelapor();
        Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        $response = $this->actingAs($user)->post('/pelapor/pengaduan', [
            'kategori' => 'Kekerasan terhadap Perempuan',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'Bandung',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('Kronologi kejadian untuk pengujian laporan. ', 3),
            'foto' => [UploadedFile::fake()->image('bukti.jpg')],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('laporan', 1);
    }

    public function test_report_validation_requires_long_text_with_letters_a_location_and_a_photo(): void
    {
        $this->useTemporaryPublicStorage();
        $user = $this->createPelapor();

        $response = $this->actingAs($user)->post('/pelapor/pengaduan', [
            'kategori' => 'Kekerasan terhadap Anak',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'ABC',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('123! ', 25),
        ]);

        $response->assertSessionHasErrors(['lokasi_kejadian', 'kronologi', 'foto']);
        $this->assertDatabaseCount('laporan', 0);
    }

    public function test_report_rejects_document_uploads(): void
    {
        $this->useTemporaryPublicStorage();
        $user = $this->createPelapor();

        $response = $this->actingAs($user)->post('/pelapor/pengaduan', [
            'kategori' => 'Kekerasan terhadap Anak',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'Jalan Melati 10',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('Kronologi kejadian untuk pengujian laporan. ', 3),
            'foto' => [UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf')],
        ]);

        $response->assertSessionHasErrors('foto.0');
        $this->assertDatabaseCount('laporan', 0);
    }

    public function test_report_accepts_three_images_but_rejects_a_fourth(): void
    {
        $this->useTemporaryPublicStorage();
        $user = $this->createPelapor();
        $payload = [
            'kategori' => 'Kekerasan terhadap Anak',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'Koordinat -7.2, 112.7',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('Kronologi kejadian untuk pengujian laporan. ', 3),
        ];

        $this->actingAs($user)->post('/pelapor/pengaduan', $payload + [
            'foto' => [
                UploadedFile::fake()->image('bukti-1.jpg'),
                UploadedFile::fake()->image('bukti-2.jpeg'),
                UploadedFile::fake()->image('bukti-3.png'),
            ],
        ])->assertRedirect();

        $this->assertDatabaseCount('laporan', 1);
        $this->assertCount(3, explode(', ', Laporan::firstOrFail()->bukti_lampiran));

        $secondUser = $this->createPelapor('empat@example.com');
        $this->actingAs($secondUser)->post('/pelapor/pengaduan', $payload + [
            'foto' => [
                UploadedFile::fake()->image('foto-1.jpg'),
                UploadedFile::fake()->image('foto-2.jpg'),
                UploadedFile::fake()->image('foto-3.jpg'),
                UploadedFile::fake()->image('foto-4.jpg'),
            ],
        ])->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('laporan', 1);
    }

    public function test_report_cannot_be_edited_after_verification_starts(): void
    {
        $user = $this->createPelapor();
        $laporan = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Sedang Diproses',
        ]);

        $this->actingAs($user)->get('/pelapor/pengaduan/' . $laporan->id_laporan . '/edit')
            ->assertRedirect('/pelapor/pengaduan/' . $laporan->id_laporan);

        $this->actingAs($user)->put('/pelapor/pengaduan/' . $laporan->id_laporan, [
            'kategori' => 'Kekerasan terhadap Perempuan',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'Jalan Melati 10',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('Perubahan yang tidak seharusnya tersimpan. ', 3),
        ])->assertRedirect('/pelapor/pengaduan/' . $laporan->id_laporan);

        $this->assertDatabaseHas('laporan', ['id_laporan' => $laporan->id_laporan, 'kategori' => 'Kekerasan terhadap Anak']);
    }

    public function test_pelapor_cannot_delete_a_submitted_report(): void
    {
        $user = $this->createPelapor();
        $laporan = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Sedang Diproses',
        ]);

        $this->actingAs($user)->delete('/pelapor/pengaduan/' . $laporan->id_laporan)
            ->assertRedirect('/pelapor/pengaduan')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('laporan', ['id_laporan' => $laporan->id_laporan]);
    }

    public function test_admin_can_publish_a_status_message_that_only_the_report_owner_sees(): void
    {
        $admin = User::create([
            'name' => 'Admin SAPA',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);
        $user = $this->createPelapor();
        $laporan = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        $this->actingAs($admin)->put('/laporan/' . $laporan->id_laporan . '/status', [
            'status_penanganan' => 'Sedang Diproses',
            'catatan_admin' => 'Catatan internal rahasia',
            'pesan_pelapor' => 'Petugas akan menghubungi Anda.',
        ])->assertRedirect('/laporan');

        $this->actingAs($user)->get('/pelapor/pengaduan/' . $laporan->id_laporan)
            ->assertSee('Petugas akan menghubungi Anda.')
            ->assertDontSee('Catatan internal rahasia');
        $this->actingAs($user)->get('/pelapor')->assertSee('Sedang Diproses');

        $otherUser = $this->createPelapor('penerima-lain@example.com');
        $this->actingAs($otherUser)->get('/pelapor')->assertDontSee('Petugas akan menghubungi Anda.');
    }

    public function test_status_filter_only_returns_the_current_users_matching_reports(): void
    {
        $user = $this->createPelapor();
        $otherUser = $this->createPelapor('oranglain@example.com');
        Laporan::create(['id_pengguna' => $user->id, 'kategori' => 'Milik Rani', 'status_penanganan' => 'Selesai']);
        Laporan::create(['id_pengguna' => $user->id, 'kategori' => 'Laporan Aktif', 'status_penanganan' => 'Menunggu Verifikasi']);
        Laporan::create(['id_pengguna' => $otherUser->id, 'kategori' => 'Data Privat', 'status_penanganan' => 'Selesai']);

        $this->actingAs($user)->get('/pelapor/pengaduan?status=Selesai')
            ->assertOk()
            ->assertSee('Milik Rani')
            ->assertDontSee('Laporan Aktif')
            ->assertDontSee('Data Privat');
    }

    public function test_profile_can_be_updated_without_changing_the_readonly_nik(): void
    {
        $user = $this->createPelapor();
        $user->update(['nik' => '3201010101010001']);

        $this->actingAs($user)->put('/pelapor/profil', [
            'name' => 'Rani Baru',
            'no_hp' => '081234567891',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Alamat baru',
        ])->assertRedirect('/pelapor/profil');

        $this->assertDatabaseHas('user', [
            'id' => $user->id,
            'name' => 'Rani Baru',
            'nik' => '3201010101010001',
        ]);
    }

    public function test_profile_rejects_names_with_numbers_or_symbols(): void
    {
        $user = $this->createPelapor();

        $this->actingAs($user)->put('/pelapor/profil', [
            'name' => 'Rani@123',
            'no_hp' => '081234567891',
            'tanggal_lahir' => '1995-05-12',
            'alamat' => 'Alamat baru',
        ])->assertSessionHasErrors(['name' => 'Nama lengkap hanya boleh berisi huruf dan spasi.']);

        $this->assertDatabaseHas('user', ['id' => $user->id, 'name' => 'Rani Pelapor']);
    }

    public function test_admin_can_complete_an_open_report_but_completed_reports_are_locked(): void
    {
        $admin = User::create([
            'name' => 'Admin Selesai',
            'email' => 'admin-selesai@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);
        $user = $this->createPelapor();
        $completed = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Selesai',
        ]);

        $this->actingAs($admin)->get('/laporan/' . $completed->id_laporan)
            ->assertOk()
            ->assertSee('Laporan sudah selesai');
        $this->actingAs($admin)->put('/laporan/' . $completed->id_laporan . '/status', [
            'status_penanganan' => 'Ditolak',
            'catatan_admin' => 'Percobaan perubahan setelah selesai',
        ])->assertRedirect('/laporan/' . $completed->id_laporan)->assertSessionHas('error');

        $active = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Perempuan',
            'status_penanganan' => 'Sedang Diproses',
        ]);
        $this->actingAs($admin)->get('/laporan/' . $active->id_laporan)
            ->assertOk()
            ->assertSee('Sedang Diproses / Diverifikasi')
            ->assertSee('<option value="Selesai">Selesai</option>', false);
        $this->actingAs($admin)->put('/laporan/' . $active->id_laporan . '/status', [
            'status_penanganan' => 'Selesai',
            'pesan_pelapor' => 'Laporan telah selesai ditangani.',
        ])->assertRedirect('/laporan');

        $this->assertDatabaseHas('laporan', ['id_laporan' => $completed->id_laporan, 'status_penanganan' => 'Selesai']);
        $this->assertDatabaseHas('laporan', ['id_laporan' => $active->id_laporan, 'status_penanganan' => 'Selesai']);
    }

    public function test_rejected_report_remains_visible_but_does_not_block_a_new_report(): void
    {
        $this->useTemporaryPublicStorage();
        $user = $this->createPelapor();
        $ditolak = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Ditolak',
            'pesan_pelapor' => 'Alasan penolakan yang dapat dibaca pengguna.',
            'riwayat_status' => [[
                'status' => 'Ditolak',
                'pesan' => 'Alasan penolakan yang dapat dibaca pengguna.',
                'pada' => '2026-10-01T10:00:00+00:00',
                'oleh' => 'Admin SAPA',
            ]],
        ]);

        $this->actingAs($user)->get('/pelapor/pengaduan/' . $ditolak->id_laporan)
            ->assertOk()
            ->assertSee('Ditolak')
            ->assertSee('Alasan penolakan yang dapat dibaca pengguna.');
        $this->actingAs($user)->get('/pelapor/pengaduan/' . $ditolak->id_laporan . '/edit')
            ->assertRedirect('/pelapor/pengaduan/' . $ditolak->id_laporan);
        $this->actingAs($user)->get('/pelapor/pengaduan')->assertSee('Buat Pengaduan');
        $this->actingAs($user)->get('/pelapor/pengaduan/buat')->assertOk();

        $this->actingAs($user)->post('/pelapor/pengaduan', [
            'kategori' => 'Kekerasan terhadap Perempuan',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'Jalan Melati 10',
            'tanggal_kejadian' => '2026-09-20',
            'kronologi' => str_repeat('Kronologi baru setelah laporan sebelumnya ditolak. ', 3),
            'foto' => [UploadedFile::fake()->image('bukti-baru.jpg')],
        ])->assertRedirect();

        $this->assertDatabaseHas('laporan', ['id_laporan' => $ditolak->id_laporan, 'status_penanganan' => 'Ditolak']);
        $this->assertDatabaseCount('laporan', 2);
    }

    public function test_pelapor_can_read_delete_one_and_bulk_delete_read_notifications_without_changing_reports(): void
    {
        $user = $this->createPelapor();
        Cache::forget('pelapor-notifications:' . $user->id);
        $laporan = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'status_penanganan' => 'Selesai',
            'riwayat_status' => [
                ['status' => 'Sedang Diproses', 'pesan' => 'Pesan notifikasi pertama.', 'pada' => '2026-09-30T10:00:00+00:00'],
                ['status' => 'Selesai', 'pesan' => 'Pesan notifikasi selesai.', 'pada' => '2026-10-01T10:00:00+00:00'],
                ['status' => 'Selesai', 'pesan' => 'Pembaruan lama yang masih unread.', 'pada' => '2026-10-01T11:00:00+00:00'],
            ],
        ]);

        $this->actingAs($user)->get('/pelapor')
            ->assertOk()
            ->assertSee('Belum dibaca')
            ->assertSee('Pesan notifikasi pertama.')
            ->assertSee('Pesan notifikasi selesai.');

        $this->actingAs($user)->post('/pelapor/notifikasi/' . $laporan->id_laporan . '/0/baca')
            ->assertRedirect('/pelapor/pengaduan/' . $laporan->id_laporan);
        $this->assertTrue(Cache::get('pelapor-notifications:' . $user->id)['read'][$laporan->id_laporan . ':0']);

        $this->actingAs($user)->delete('/pelapor/notifikasi/' . $laporan->id_laporan . '/1')
            ->assertSessionHas('success');
        $this->actingAs($user)->get('/pelapor')->assertDontSee('Pesan notifikasi selesai.');

        $this->actingAs($user)->delete('/pelapor/notifikasi-terbaca')->assertSessionHas('success');
        $this->actingAs($user)->get('/pelapor')->assertDontSee('Pesan notifikasi pertama.');
        $this->actingAs($user)->get('/pelapor')->assertSee('Pembaruan lama yang masih unread.')->assertSee('Belum dibaca');

        $laporan->refresh();
        $this->assertSame('Selesai', $laporan->status_penanganan);
        $this->assertCount(3, $laporan->riwayat_status);
        $this->assertDatabaseHas('laporan', ['id_laporan' => $laporan->id_laporan]);
    }

    public function test_long_chronology_is_preserved_and_wrapped_in_pelapor_and_admin_details(): void
    {
        $admin = User::create([
            'name' => 'Admin Detail',
            'email' => 'admin-detail@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);
        $user = $this->createPelapor();
        $chronology = str_repeat('KronologiTanpaSpasi123', 200);
        $laporan = Laporan::create([
            'id_pengguna' => $user->id,
            'nama_pelapor' => $user->name,
            'kategori' => 'Kekerasan terhadap Anak',
            'kronologi' => $chronology,
            'status_penanganan' => 'Sedang Diproses',
        ]);

        $this->actingAs($user)->get('/pelapor/pengaduan/' . $laporan->id_laporan)
            ->assertOk()
            ->assertSee($chronology)
            ->assertSee('overflow-wrap:anywhere', false)
            ->assertSee('minmax(0,1fr)', false);

        $this->actingAs($admin)->get('/laporan/' . $laporan->id_laporan)
            ->assertOk()
            ->assertSee($chronology)
            ->assertSee('overflow-wrap:anywhere', false)
            ->assertSee('minmax(0,2fr)', false);
    }

    public function test_mobile_birth_date_format_renders_as_a_web_date_input(): void
    {
        $user = $this->createPelapor();
        $user->update(['tanggal_lahir' => '12/05/1995']);

        $this->actingAs($user)->get('/pelapor/profil')
            ->assertOk()
            ->assertSee('value="1995-05-12"', false);
    }

    private function createPelapor(string $email = 'pelapor@example.com'): User
    {
        return User::create([
            'name' => 'Rani Pelapor',
            'email' => $email,
            'password' => Hash::make('Password123!'),
            'role' => 'pengguna',
        ]);
    }

    private function useTemporaryPublicStorage(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sapa-web-test-' . Str::random(16);
        File::ensureDirectoryExists($root);
        Storage::set('local', Storage::build([
            'driver' => 'local',
            'root' => $root,
        ]));
        $this->beforeApplicationDestroyed(static fn () => File::deleteDirectory($root));
    }
}
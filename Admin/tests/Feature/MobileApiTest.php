<?php

namespace Tests\Feature;

use App\Models\Informasi;
use App\Models\Laporan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_and_login_issue_a_mobile_token(): void
    {
        $registration = $this->postJson('/api/register', [
            'name' => 'Pelapor Uji',
            'nik' => '3201010101010001',
            'tanggal_lahir' => '01-01-2000',
            'alamat' => 'Bandung',
            'email' => 'pelapor@example.test',
            'no_hp' => '081234567890',
            'password' => 'password123',
        ]);
        $registration->assertCreated()
            ->assertJsonPath('data.role', 'pengguna')
            ->assertJsonMissingPath('data.password');

        $this->postJson('/api/login', [
            'email' => '081234567890',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Pelapor Uji')
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_database_seeder_creates_the_configured_admin_account(): void
    {
        Config::set('admin.email', 'admin@example.test');
        Config::set('admin.password', 'a-secure-test-password');

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('a-secure-test-password', $admin->password));

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'a-secure-test-password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_mobile_reports_are_authenticated_and_scoped_to_the_owner(): void
    {
        Storage::fake('local');
        $owner = $this->createUser('owner@example.test');
        $other = $this->createUser('other@example.test');
        $token = $owner->createToken('flutter-app')->plainTextToken;

        $created = $this->withToken($token)->post('/api/lapor', [
            'kategori' => 'Kekerasan',
            'sebagai' => 'Korban',
            'lokasi_kejadian' => 'Bandung',
            'tanggal_kejadian' => '2026-11-01',
            'kronologi' => 'Laporan untuk pengujian.',
            'bukti_lampiran' => [UploadedFile::fake()->image('bukti.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated();
        $reportId = $created->json('data.id_laporan');
        $this->assertNotEmpty($created->json('data.bukti_lampiran'));
        Storage::disk('local')->assertExists($created->json('data.bukti_lampiran'));

        Laporan::create([
            'id_pengguna' => $other->id,
            'kategori' => 'Lainnya',
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        $reportList = $this->withToken($token)->getJson('/api/riwayat');
        $reportList
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_pengguna', $owner->id);
        $this->withToken($token)->getJson('/api/laporan-terakhir')
            ->assertOk()
            ->assertJsonPath('data.id_laporan', $reportId);

        Laporan::where('id_laporan', $reportId)->update([
            'catatan_admin' => 'Rahasia internal petugas.',
        ]);
        $reportList = $this->withToken($token)->getJson('/api/riwayat')
            ->assertOk()
            ->assertJsonMissingPath('data.0.catatan_admin');
        $reportList->assertDontSee('Rahasia internal petugas.');
        $latestReport = $this->withToken($token)->getJson('/api/laporan-terakhir')
            ->assertOk()
            ->assertJsonMissingPath('data.catatan_admin');
        $latestReport->assertDontSee('Rahasia internal petugas.');

        $this->withToken($token)->getJson("/api/notifikasi/{$owner->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status_penanganan', 'Menunggu Verifikasi')
            ->assertJsonPath('data.0.pesan', 'Laporan Anda telah diterima dan menunggu verifikasi.')
            ->assertJsonMissingPath('data.0.catatan_admin');

        Laporan::where('id_laporan', $reportId)->update([
            'status_penanganan' => 'Sedang Diproses',
            'catatan_admin' => 'Petugas sedang menindaklanjuti.',
            'pesan_pelapor' => 'Petugas sedang meninjau laporan Anda.',
        ]);
        $notificationResponse = $this->withToken($token)->getJson("/api/notifikasi/{$owner->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.pesan', 'Petugas sedang meninjau laporan Anda.')
            ->assertJsonMissingPath('data.0.catatan_admin');
        $notificationResponse->assertDontSee('Petugas sedang menindaklanjuti.');
        $this->withToken($token)->deleteJson("/api/notifikasi/{$owner->id}/{$reportId}")
            ->assertOk();
        $this->withToken($token)->getJson("/api/notifikasi/{$owner->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_message_is_shown_for_a_report_that_is_still_waiting(): void
    {
        $owner = $this->createUser('message-owner@example.test');
        $admin = User::create([
            'name' => 'Admin SAPA',
            'email' => 'message-admin@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);
        $report = Laporan::create([
            'id_pengguna' => $owner->id,
            'kategori' => 'Kekerasan',
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);
        $token = $owner->createToken('flutter-app')->plainTextToken;

        $this->actingAs($admin)->put("/laporan/{$report->id_laporan}/status", [
            'status_penanganan' => 'Menunggu Verifikasi',
            'pesan_pelapor' => 'apa lu',
        ])->assertRedirect('/laporan');

        $this->assertDatabaseHas('laporan', [
            'id_laporan' => $report->id_laporan,
            'pesan_pelapor' => 'apa lu',
        ]);

        Laporan::where('id_laporan', $report->id_laporan)->update([
            'pesan_pelapor' => null,
        ]);
        Auth::forgetGuards();
        $this->withToken($token)->getJson("/api/notifikasi/{$owner->id}")
            ->assertOk()
            ->assertJsonPath('data.0.pesan', 'apa lu')
            ->assertJsonMissing(['pesan' => 'null'])
            ->assertJsonMissingPath('data.0.riwayat_status');
    }

    public function test_mobile_user_can_delete_only_an_unprocessed_report(): void
    {
        Storage::fake('local');
        $user = $this->createUser('delete-report@example.test');
        $token = $user->createToken('flutter-app')->plainTextToken;
        $pending = Laporan::create([
            'id_pengguna' => $user->id,
            'nomor_laporan' => 'LAP-PENDING-001',
            'kategori' => 'Kekerasan',
            'status_penanganan' => 'Menunggu Verifikasi',
            'bukti_lampiran' => 'laporan/1/bukti.jpg',
        ]);
        Storage::disk('local')->put('laporan/1/bukti.jpg', 'bukti');

        $this->withToken($token)->deleteJson("/api/laporan/{$pending->id_laporan}")
            ->assertOk()
            ->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('laporan', ['id_laporan' => $pending->id_laporan]);
        Storage::disk('local')->assertMissing('laporan/1/bukti.jpg');

        $processing = Laporan::create([
            'id_pengguna' => $user->id,
            'kategori' => 'Kekerasan',
            'status_penanganan' => 'Sedang Diproses',
        ]);
        $this->withToken($token)->deleteJson("/api/laporan/{$processing->id_laporan}")
            ->assertUnprocessable();
        $this->assertDatabaseHas('laporan', ['id_laporan' => $processing->id_laporan]);
    }

    public function test_mobile_information_only_returns_published_content_available_today(): void
    {
        $published = Informasi::create([
            'judul' => 'Konten terbit',
            'konten' => 'Konten untuk pengguna.',
            'status' => 'published',
            'tanggal_publish' => today(),
        ]);
        Informasi::create([
            'judul' => 'Konten draf',
            'konten' => 'Belum untuk pengguna.',
            'status' => 'draf',
            'tanggal_publish' => today(),
        ]);
        Informasi::create([
            'judul' => 'Konten mendatang',
            'konten' => 'Belum waktunya tampil.',
            'status' => 'published',
            'tanggal_publish' => today()->addDay(),
        ]);

        $this->getJson('/api/informasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.0.judul', 'Konten terbit');
    }

    public function test_mobile_information_includes_a_url_for_uploaded_videos(): void
    {
        $published = Informasi::create([
            'judul' => 'Video terbit',
            'konten' => 'Video untuk pengguna.',
            'status' => 'published',
            'tanggal_publish' => today(),
            'video_path' => 'informasi/videos/edukasi.mp4',
        ]);

        $this->getJson('/api/informasi')
            ->assertOk()
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.0.video_file_url', 'http://localhost/storage/informasi/videos/edukasi.mp4');
    }

    public function test_mobile_report_numbers_are_sequential_per_day(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 2)->setTime(12, 0));
        $user = $this->createUser('numbering@example.test');
        $token = $user->createToken('flutter-app')->plainTextToken;
        $report = [
            'kategori' => 'Kekerasan',
            'lokasi_kejadian' => 'Bandung',
            'tanggal_kejadian' => '2026-11-02',
            'kronologi' => 'Laporan untuk pengujian nomor.',
        ];

        $this->withToken($token)->postJson('/api/lapor', $report)
            ->assertCreated()
            ->assertJsonPath('data.nomor_laporan', 'LAP-20261102-001');
        $this->withToken($token)->postJson('/api/lapor', array_merge($report, [
            'kronologi' => 'Laporan kedua untuk pengujian nomor.',
        ]))->assertCreated()
            ->assertJsonPath('data.nomor_laporan', 'LAP-20261102-002');

        $this->travelTo(now()->addDay());
        $this->withToken($token)->postJson('/api/lapor', array_merge($report, [
            'kronologi' => 'Laporan hari berikutnya untuk pengujian nomor.',
        ]))->assertCreated()
            ->assertJsonPath('data.nomor_laporan', 'LAP-20261103-001');
    }

    public function test_mobile_report_endpoints_require_authentication(): void
    {
        $this->getJson('/api/riwayat')->assertUnauthorized();
        $this->postJson('/api/lapor', [])->assertUnauthorized();
    }

    public function test_disabled_accounts_cannot_log_in_or_use_existing_mobile_tokens(): void
    {
        $user = $this->createUser('disabled@example.test');
        $token = $user->createToken('flutter-app')->plainTextToken;
        $admin = User::create([
            'name' => 'Admin SAPA',
            'email' => 'admin-disable@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->get('/pengguna')
            ->assertOk()
            ->assertSee('Status Akun')
            ->assertSee('Aktif')
            ->assertSee('Nonaktifkan');
        $this->patch("/pengguna/{$user->id}/status")
            ->assertRedirect();
        $this->assertDatabaseHas('user', ['id' => $user->id, 'is_active' => false]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertUnauthorized();
        Auth::logout();
        $this->withToken($token)->getJson('/api/riwayat')->assertUnauthorized();

        $this->actingAs($admin);
        $this->patch("/pengguna/{$user->id}/status")->assertRedirect();
        $this->assertDatabaseHas('user', ['id' => $user->id, 'is_active' => true]);
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk();
    }

    public function test_mobile_logout_revokes_the_current_token(): void
    {
        $user = $this->createUser('logout@example.test');
        $token = $user->createToken('flutter-app');

        $this->withToken($token->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_mobile_profile_and_password_can_only_be_changed_by_the_owner(): void
    {
        $owner = $this->createUser('owner@example.test');
        $token = $owner->createToken('flutter-app')->plainTextToken;

        $this->withToken($token)->putJson("/api/pengguna/{$owner->id}/profil", [
            'name' => 'Nama Baru',
            'email' => 'baru@example.test',
            'no_hp' => '081234567891',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru');

        $this->withToken($token)->putJson("/api/pengguna/{$owner->id}/password", [
            'password_lama' => 'password123',
            'password_baru' => 'newpassword123',
            'password_baru_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->withToken($token)->getJson('/api/notifikasi/'.($owner->id + 1))
            ->assertForbidden();
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Pelapor Uji',
            'nik' => str_pad((string) random_int(1, 9999999999999999), 16, '0', STR_PAD_LEFT),
            'email' => $email,
            'no_hp' => '08'.random_int(100000000, 999999999),
            'role' => 'pengguna',
            'password' => Hash::make('password123'),
        ]);
    }
}

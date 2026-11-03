<?php

namespace Tests\Feature;

use App\Models\Informasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminInformasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create([
            'name' => 'Admin Uji',
            'email' => 'admin-informasi@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
        ]));
    }

    public function test_admin_can_upload_video_and_it_is_embedded_on_the_public_page(): void
    {
        $this->post(route('admin.informasi.store'), [
            'judul' => 'Edukasi video',
            'konten' => 'Deskripsi video.',
            'status' => 'published',
            'tanggal_publish' => today()->toDateString(),
            'video' => UploadedFile::fake()->create('edukasi.mp4', 100, 'video/mp4'),
        ])->assertRedirect(route('admin.informasi.index'))->assertSessionHasNoErrors();

        $informasi = Informasi::firstOrFail();
        $this->assertStringStartsWith('informasi/videos/', $informasi->video_path);
        Storage::disk('public')->assertExists($informasi->video_path);

        $this->actingAs(User::create([
            'name' => 'Pelapor Uji',
            'email' => 'pelapor-informasi@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'pengguna',
        ]));
        $this->get(route('pelapor.information'))
            ->assertOk()
            ->assertSee('<video controls playsinline', false)
            ->assertSee('/storage/'.$informasi->video_path, false);
    }

    public function test_admin_can_remove_existing_image_and_video_from_an_information_item(): void
    {
        $gambar = UploadedFile::fake()->image('edukasi.jpg');
        $video = UploadedFile::fake()->create('edukasi.webm', 100, 'video/webm');
        $gambarPath = $gambar->store('informasi', 'public');
        $videoPath = $video->store('informasi/videos', 'public');
        $informasi = Informasi::create([
            'judul' => 'Edukasi',
            'konten' => 'Isi.',
            'status' => 'draf',
            'tanggal_publish' => today(),
            'gambar' => $gambarPath,
            'video_path' => $videoPath,
        ]);

        $this->put(route('admin.informasi.update', $informasi), [
            'judul' => $informasi->judul,
            'konten' => $informasi->konten,
            'status' => $informasi->status,
            'tanggal_publish' => today()->toDateString(),
            'hapus_gambar' => '1',
            'hapus_video' => '1',
        ])->assertRedirect(route('admin.informasi.index'))->assertSessionHasNoErrors();

        $this->assertNull($informasi->fresh()->gambar);
        $this->assertNull($informasi->fresh()->video_path);
        Storage::disk('public')->assertMissing([$gambarPath, $videoPath]);
    }

    public function test_upload_rejects_unsupported_video_formats_and_video_links(): void
    {
        $this->from(route('admin.informasi.create'))->post(route('admin.informasi.store'), [
            'judul' => 'Edukasi',
            'konten' => 'Isi.',
            'status' => 'draf',
            'tanggal_publish' => today()->toDateString(),
            'video' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.informasi.create'))->assertSessionHasErrors('video');

        $this->from(route('admin.informasi.create'))->post(route('admin.informasi.store'), [
            'judul' => 'Edukasi',
            'konten' => 'Isi.',
            'status' => 'draf',
            'tanggal_publish' => today()->toDateString(),
            'video_url' => 'https://example.com/video',
        ])->assertRedirect(route('admin.informasi.create'))->assertSessionHasErrors('video_url');
    }

    public function test_supported_video_links_are_embedded_instead_of_showing_unavailable(): void
    {
        $informasi = Informasi::create([
            'judul' => 'Video YouTube',
            'konten' => 'Isi.',
            'status' => 'published',
            'tanggal_publish' => today(),
            'video_url' => 'https://youtu.be/abcdefghijk',
        ]);

        $this->actingAs(User::create([
            'name' => 'Pelapor Uji',
            'email' => 'pelapor-link@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'pengguna',
        ]));
        $this->get(route('pelapor.information'))
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk', false);
    }
}

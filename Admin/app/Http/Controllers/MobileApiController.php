<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use App\Models\Laporan;
use App\Services\NomorLaporanGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MobileApiController extends Controller
{
    public function informations()
    {
        $informasis = Informasi::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('tanggal_publish')
                    ->orWhereDate('tanggal_publish', '<=', today());
            })
            ->latest('tanggal_publish')
            ->get(['id', 'judul', 'konten', 'gambar', 'tanggal_publish']);

        return response()->json(['status' => 'success', 'data' => $informasis]);
    }

    public function reports(Request $request)
    {
        $reports = Laporan::where('id_pengguna', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Laporan $report) => $report->makeHidden('catatan_admin'));

        return response()->json(['status' => 'success', 'data' => $reports]);
    }

    public function latestReport(Request $request)
    {
        $report = Laporan::where('id_pengguna', $request->user()->id)
            ->latest()
            ->first();

        if (! $report) {
            return response()->json([
                'status' => 'empty',
                'message' => 'Belum ada laporan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $report->makeHidden('catatan_admin'),
        ]);
    }

    public function storeReport(Request $request, NomorLaporanGenerator $nomorLaporanGenerator)
    {
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100'],
            'sebagai' => ['nullable', 'string', 'max:50'],
            'lokasi_kejadian' => ['required', 'string', 'max:255'],
            'tanggal_kejadian' => ['required', 'date'],
            'kronologi' => ['required', 'string', 'max:10000'],
            'bukti_lampiran' => ['nullable', 'array', 'max:3'],
            'bukti_lampiran.*' => ['image', 'max:2048'],
        ]);

        $report = Laporan::create([
            'nomor_laporan' => $nomorLaporanGenerator->generate(),
            'id_pengguna' => $request->user()->id,
            'nama_pelapor' => $request->user()->name,
            'kategori' => $validated['kategori'],
            'sebagai' => $validated['sebagai'] ?? null,
            'lokasi_kejadian' => $validated['lokasi_kejadian'],
            'tanggal_kejadian' => $validated['tanggal_kejadian'],
            'kronologi' => $validated['kronologi'],
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        $paths = [];
        foreach ($request->file('bukti_lampiran', []) as $file) {
            $paths[] = $file->store('laporan/'.$report->id_laporan, 'local');
        }

        if ($paths !== []) {
            $report->bukti_lampiran = implode(',', $paths);
            $report->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil terkirim ke admin.',
            'data' => $report->fresh()->makeHidden('catatan_admin'),
        ], 201);
    }

    public function updateReport(Request $request, int $id)
    {
        $report = $this->ownedReport($request, $id);
        abort_unless($report->status_penanganan === 'Menunggu Verifikasi', 422, 'Laporan yang sudah diproses tidak dapat diubah.');

        $validated = $request->validate([
            'kategori' => ['sometimes', 'required', 'string', 'max:100'],
            'lokasi' => ['sometimes', 'required', 'string', 'max:255'],
            'lokasi_kejadian' => ['sometimes', 'required', 'string', 'max:255'],
            'kronologi' => ['sometimes', 'required', 'string', 'max:10000'],
        ]);

        if (array_key_exists('lokasi', $validated)) {
            $validated['lokasi_kejadian'] = $validated['lokasi'];
            unset($validated['lokasi']);
        }

        $report->fill($validated)->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil diperbarui.',
            'data' => $report->fresh()->makeHidden('catatan_admin'),
        ]);
    }

    public function deleteReport(Request $request, int $id)
    {
        $report = $this->ownedReport($request, $id);
        abort_unless(
            $report->status_penanganan === 'Menunggu Verifikasi',
            422,
            'Laporan hanya dapat dihapus sebelum mulai diproses.'
        );
        $directory = 'laporan/'.$report->id_laporan.'/';
        $paths = array_values(array_filter(
            array_map('trim', explode(',', (string) $report->bukti_lampiran)),
            fn (string $path) => str_starts_with($path, $directory)
        ));

        $report->delete();
        Storage::disk('local')->delete($paths);

        return response()->json(['status' => 'success', 'message' => 'Laporan berhasil dihapus.']);
    }

    public function notifications(Request $request, int $userId)
    {
        abort_unless($request->user()->id === $userId, 403);

        $notifications = Laporan::where('id_pengguna', $userId)
            ->where('notifikasi_dihapus', false)
            ->latest('updated_at')
            ->get([
                'id_laporan',
                'nomor_laporan',
                'status_penanganan',
                'pesan_pelapor',
                'riwayat_status',
                'created_at',
                'updated_at',
            ])
            ->map(function (Laporan $laporan) {
                $pesanPelapor = trim((string) $laporan->pesan_pelapor);
                if ($pesanPelapor === '' || strtolower($pesanPelapor) === 'null') {
                    $pesanRiwayat = collect($laporan->riwayat_status ?? [])
                        ->reverse()
                        ->first(function ($entry) {
                            if (! is_array($entry)) {
                                return false;
                            }
                            $pesan = trim((string) ($entry['pesan'] ?? ''));

                            return $pesan !== '' && strtolower($pesan) !== 'null';
                        });
                    $pesanPelapor = trim((string) ($pesanRiwayat['pesan'] ?? ''));
                }

                $laporan->setAttribute(
                    'pesan',
                    $pesanPelapor !== '' ? $pesanPelapor : match ($laporan->status_penanganan) {
                        'Menunggu Verifikasi' => 'Laporan Anda telah diterima dan menunggu verifikasi.',
                        'Sedang Diproses', 'Diproses' => 'Laporan Anda sedang ditindaklanjuti oleh petugas.',
                        'Selesai' => 'Laporan Anda telah selesai ditangani.',
                        'Ditolak' => 'Laporan Anda ditolak. Silakan periksa detail laporan untuk informasi lebih lanjut.',
                        default => 'Status laporan Anda telah diperbarui.',
                    }
                );
                $laporan->setAttribute(
                    'dapat_dihapus',
                    $laporan->status_penanganan === 'Menunggu Verifikasi'
                );

                return $laporan->makeHidden('riwayat_status');
            });

        return response()->json(['status' => 'success', 'data' => $notifications]);
    }

    public function dismissNotification(Request $request, int $userId, int $reportId)
    {
        abort_unless($request->user()->id === $userId, 403);
        $report = $this->ownedReport($request, $reportId);
        $report->notifikasi_dihapus = true;
        $report->save();

        return response()->json(['status' => 'success', 'message' => 'Notifikasi berhasil disembunyikan.']);
    }

    public function updateProfile(Request $request, int $userId)
    {
        $user = $request->user();
        abort_unless($user->id === $userId, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'min:10', 'max:20', Rule::unique('user', 'no_hp')->ignore($user->id)],
        ]);

        $user->fill($validated)->save();

        return response()->json(['status' => 'success', 'data' => $user->fresh()]);
    }

    public function updatePassword(Request $request, int $userId)
    {
        $user = $request->user();
        abort_unless($user->id === $userId, 403);

        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['password_lama'], $user->password)) {
            return response()->json(['message' => 'Password lama tidak sesuai.'], 422);
        }

        $user->password = Hash::make($validated['password_baru']);
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Password berhasil diperbarui.']);
    }

    private function ownedReport(Request $request, int $id): Laporan
    {
        return Laporan::where('id_pengguna', $request->user()->id)
            ->findOrFail($id);
    }
}

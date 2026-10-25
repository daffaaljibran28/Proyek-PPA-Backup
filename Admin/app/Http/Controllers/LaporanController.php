<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    /**
     * Menyimpan Laporan Baru dengan Pencegahan Spam & Redundansi
     */
    public function store(Request $request)
    {
        $tanggal = $request->tanggal_kejadian;
        try {
            if ($tanggal && str_contains($tanggal, '-')) {
                $parts = explode('-', $tanggal);
                if (strlen($parts[0]) == 2) {
                    $tanggal = Carbon::createFromFormat('d-m-Y', $request->tanggal_kejadian)->format('Y-m-d');
                }
            }
        } catch (\Exception $e) {
            $tanggal = date('Y-m-d');
        }

        // ========================================================
        // PENCEGAHAN REDUNDANSI & SPAM (Maksimal 10 menit terakhir)
        // ========================================================
        $idPengguna = $request->id_pengguna ?? 1;
        $isSpam = Laporan::where('id_pengguna', $idPengguna)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->where(function ($query) use ($request) {
                $query->where('kronologi', $request->kronologi)
                      ->orWhere(function ($q) use ($request) {
                          $q->where('kategori', $request->kategori)
                            ->where('lokasi_kejadian', $request->lokasi_kejadian);
                      });
            })
            ->exists();

        if ($isSpam) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Laporan serupa sudah pernah dikirimkan baru-baru ini. Mohon tunggu beberapa saat untuk mencegah spam.',
            ], 422);
        }
        // ========================================================

        $nomorLaporan = 'LAP-' . date('Ymd') . '-' . rand(100, 999);

        $laporan = Laporan::create([
            'nomor_laporan'     => $nomorLaporan,
            'id_pengguna'       => $idPengguna,
            'nama_pelapor'      => $request->nama_pelapor ?? 'Revitaaa',
            'kategori'          => $request->kategori,
            'sebagai'           => $request->sebagai,
            'lokasi_kejadian'   => $request->lokasi_kejadian,
            'tanggal_kejadian'  => $tanggal,
            'kronologi'         => $request->kronologi,
            'bukti_lampiran'    => $request->bukti_lampiran,
            'status_penanganan' => 'Menunggu Verifikasi',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Laporan berhasil dibuat',
            'data'    => $laporan,
        ], 201);
    }

    /**
     * Dashboard Admin dengan Filter Harian / Bulanan & Sinkronisasi Status
     */
    public function dashboard(Request $request)
    {
        $filter  = $request->get('filter', 'bulan'); // Default pilihan bulanan
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $bulan   = $request->get('bulan', now()->format('Y-m'));

        $query = Laporan::query();

        // Penerapan filter periode harian atau bulanan
        if ($filter === 'hari') {
            $query->whereDate('created_at', $tanggal);
        } else {
            $parts = explode('-', $bulan);
            if (count($parts) === 2) {
                $query->whereYear('created_at', $parts[0])
                      ->whereMonth('created_at', $parts[1]);
            }
        }

        // Penghitungan statistik yang seragam dan akurat (mengakomodasi status Selesai, Ditolak, & Diproses)
        $totalLaporan       = (clone $query)->count();
        $menungguVerifikasi = (clone $query)->where('status_penanganan', 'Menunggu Verifikasi')->count();
        $sedangDiproses     = (clone $query)->whereIn('status_penanganan', ['Sedang Diproses', 'Diproses'])->count();
        $selesai            = (clone $query)->where('status_penanganan', 'Selesai')->count();
        $ditolak            = (clone $query)->where('status_penanganan', 'Ditolak')->count();

        // Ambil data laporan terbaru sesuai filter periode
        $laporanTerbaru     = (clone $query)->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalLaporan',
            'menungguVerifikasi',
            'sedangDiproses',
            'selesai',
            'ditolak',
            'laporanTerbaru',
            'filter',
            'tanggal',
            'bulan'
        ));
    }

    /**
     * WEB: Daftar Seluruh Laporan
     */
    public function index(Request $request)
    {
        $query = Laporan::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_laporan', 'like', "%{$search}%")
                  ->orWhere('nama_pelapor', 'like', "%{$search}%")
                  ->orWhere('lokasi_kejadian', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $kategori = trim($request->kategori);
            $query->where(function ($q) use ($kategori) {
                $q->where('kategori', 'like', "%{$kategori}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_penanganan', $request->status);
        }

        $laporans = $query->latest()->paginate(10)->withQueryString();

        return view('laporan.index', compact('laporans'));
    }

    /**
     * Detail Satu Laporan
     */
    public function show($id)
    {
        $laporan = Laporan::where('id_laporan', $id)->firstOrFail();
        return view('laporan.show', compact('laporan'));
    }

    /**
     * Berkas Bukti Lampiran
     */
    public function evidence(Request $request, $id, int $index)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $laporan = Laporan::where('id_laporan', $id)->firstOrFail();
        $files = array_values(array_filter(array_map('trim', explode(',', (string) $laporan->bukti_lampiran))));
        $path = $files[$index] ?? abort(404);

        abort_unless(str_starts_with($path, 'laporan/' . $laporan->id_laporan . '/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), ['Cache-Control' => 'private, no-store'], 'inline');
    }

    /**
     * Update Status Laporan dari Admin Web (Termasuk Selesai & Ditolak)
     */
    public function updateStatus(Request $request, $id)
    {
        $laporan = Laporan::where('id_laporan', $id)->firstOrFail();
        
        $validated = $request->validate([
            'status_penanganan' => ['sometimes', 'required', 'in:Menunggu Verifikasi,Sedang Diproses,Diproses,Selesai,Ditolak'],
            'status'            => ['sometimes', 'required', 'in:Menunggu Verifikasi,Sedang Diproses,Diproses,Selesai,Ditolak'],
            'catatan_admin'     => ['nullable', 'string', 'max:5000'],
            'pesan_pelapor'     => ['nullable', 'string', 'max:2000'],
        ]);

        $statusBaru   = $validated['status_penanganan'] ?? $validated['status'] ?? $laporan->status_penanganan;
        $pesanPelapor = trim($validated['pesan_pelapor'] ?? '');
        $riwayat      = $laporan->riwayat_status ?? [];

        if ($statusBaru !== $laporan->status_penanganan || $pesanPelapor !== '') {
            $riwayat[] = [
                'status' => $statusBaru,
                'pesan'  => $pesanPelapor ?: null,
                'pada'   => now()->toIso8601String(),
                'oleh'   => auth()->user()->name,
            ];
        }

        $laporan->status_penanganan = $statusBaru;
        $laporan->catatan_admin     = $validated['catatan_admin'] ?? $laporan->catatan_admin;
        $laporan->pesan_pelapor     = $pesanPelapor !== '' ? $pesanPelapor : $laporan->pesan_pelapor;
        $laporan->riwayat_status    = $riwayat;
        $laporan->save();

        return redirect()->route('laporan.index')
            ->with('success', 'Status laporan berhasil diperbarui!');
    }
}
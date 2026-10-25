<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Informasi; // Ditambahkan untuk mengambil data dari tabel informasis
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PelaporController extends Controller
{
    private const KATEGORI = [
        'Kekerasan terhadap Perempuan',
        'Kekerasan terhadap Anak',
        'Pelecehan Seksual',
        'Kekerasan Dalam Rumah Tangga (KDRT)',
    ];

    private const PERAN_PELAPOR = ['Korban', 'Saksi', 'Keluarga Korban'];

    public function dashboard(Request $request)
    {
        $laporanQuery = Laporan::where('id_pengguna', $request->user()->id);
        $laporanTerakhir = (clone $laporanQuery)->latest()->first();
        $jumlahLaporan = (clone $laporanQuery)->count();
        $menunggu = (clone $laporanQuery)->where('status_penanganan', 'Menunggu Verifikasi')->count();
        $diproses = (clone $laporanQuery)->whereIn('status_penanganan', ['Sedang Diproses', 'Diproses'])->count();
        $selesai = (clone $laporanQuery)->where('status_penanganan', 'Selesai')->count();
        $laporanTerbaru = (clone $laporanQuery)->latest()->take(5)->get();

        return view('pelapor.dashboard', compact(
            'laporanTerakhir',
            'jumlahLaporan',
            'menunggu',
            'diproses',
            'selesai',
            'laporanTerbaru'
        ));
    }

    public function index(Request $request)
    {
        $query = Laporan::where('id_pengguna', $request->user()->id);
        $status = $request->query('status');
        if (in_array($status, ['Menunggu Verifikasi', 'Sedang Diproses', 'Selesai', 'Ditolak'], true)) {
            if ($status === 'Sedang Diproses') {
                $query->whereIn('status_penanganan', ['Sedang Diproses', 'Diproses']);
            } else {
                $query->where('status_penanganan', $status);
            }
        } else {
            $status = null;
        }

        $laporans = $query->latest()->paginate(10)->withQueryString();
        $laporanTerakhir = Laporan::where('id_pengguna', $request->user()->id)->latest()->first();
        $canCreateReport = !$laporanTerakhir || in_array($laporanTerakhir->status_penanganan, ['Selesai', 'Ditolak'], true);

        return view('pelapor.laporan.index', compact('laporans', 'status', 'canCreateReport'));
    }

    public function create(Request $request)
    {
        $laporanTerakhir = Laporan::where('id_pengguna', $request->user()->id)->latest()->first();
        if ($laporanTerakhir && !in_array($laporanTerakhir->status_penanganan, ['Selesai', 'Ditolak'], true)) {
            return redirect()->route('pelapor.laporan.show', $laporanTerakhir->id_laporan)
                ->with('error', 'Anda masih memiliki pengaduan yang sedang ditangani.');
        }

        return view('pelapor.laporan.form', [
            'laporan' => null,
            'kategori' => self::KATEGORI,
            'peranPelapor' => self::PERAN_PELAPOR,
            'fotoTersimpan' => [],
        ]);
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Cek apakah masih ada pengaduan yang aktif (belum Selesai/Ditolak)
        $laporanTerakhir = Laporan::where('id_pengguna', $userId)->latest()->first();
        if ($laporanTerakhir && !in_array($laporanTerakhir->status_penanganan, ['Selesai', 'Ditolak'], true)) {
            return redirect()->route('pelapor.laporan.show', $laporanTerakhir->id_laporan)
                ->with('error', 'Anda masih memiliki pengaduan yang sedang ditangani.');
        }

        // 2. Kuota harian per pengguna (maksimal 2 pengaduan per hari per akun)
        $countTodayUser = Laporan::where('id_pengguna', $userId)
            ->whereDate('created_at', today())
            ->count();

        if ($countTodayUser >= 2) {
            return redirect()->route('pelapor.laporan.index')
                ->with('error', 'Anda telah mencapai batas maksimal 2 pengaduan per hari. Silakan coba kembali esok hari.');
        }

        $validated = $this->validateReport($request);

        // 3. Cek duplikat kronologi (mencegah spam teks yang sama persis)
        $isDuplicate = Laporan::where('id_pengguna', $userId)
            ->where('kronologi', $validated['kronologi'])
            ->exists();

        if ($isDuplicate) {
            return back()->withInput()->withErrors([
                'kronologi' => 'Isi kronologi pengaduan ini sama persis dengan laporan Anda sebelumnya. Mohon buat uraian kronologi yang berbeda untuk menghindari redundansi data.',
            ]);
        }

        $photos = $validated['foto'];
        unset($validated['foto']);
        $nomorLaporan = $this->newReportNumber();

        $laporan = Laporan::create($validated + [
            'nomor_laporan' => $nomorLaporan,
            'id_pengguna' => $userId,
            'nama_pelapor' => $request->user()->name,
            'status_penanganan' => 'Menunggu Verifikasi',
            'riwayat_status' => [[
                'status' => 'Menunggu Verifikasi',
                'pesan' => null,
                'pada' => now()->toIso8601String(),
                'oleh' => 'Sistem',
            ]],
        ]);
        
        $this->appendPhotos($laporan, $photos);

        return redirect()->route('pelapor.laporan.show', $laporan->id_laporan)
            ->with('success', 'Pengaduan berhasil dikirim. Nomor laporan Anda ' . $laporan->nomor_laporan . '.');
    }

    public function show(Request $request, int $id)
    {
        $laporan = $this->ownedLaporan($request, $id);
        $buktiFoto = $this->photoReferences($laporan);

        return view('pelapor.laporan.show', compact('laporan', 'buktiFoto'));
    }

    public function evidence(Request $request, int $id, int $index)
    {
        $laporan = $this->ownedLaporan($request, $id);
        $path = $this->photoReferences($laporan)[$index] ?? abort(404);

        abort_unless(str_starts_with($path, 'laporan/' . $laporan->id_laporan . '/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), ['Cache-Control' => 'private, no-store'], 'inline');
    }

    public function markNotificationRead(Request $request, int $id, int $eventIndex)
    {
        [$laporan] = $this->ownedNotificationEntry($request, $id, $eventIndex);
        $notificationKey = $this->notificationEventKey($id, $eventIndex);
        $state = $this->notificationState($request);
        abort_if(isset($state['deleted'][$notificationKey]), 404);

        $state['read'][$notificationKey] = true;
        Cache::forever($this->notificationCacheKey($request), $state);

        return redirect()->route('pelapor.laporan.show', $laporan->id_laporan);
    }

    public function destroyNotification(Request $request, int $id, int $eventIndex)
    {
        $this->ownedNotificationEntry($request, $id, $eventIndex);
        $notificationKey = $this->notificationEventKey($id, $eventIndex);
        $state = $this->notificationState($request);
        $state['deleted'][$notificationKey] = true;
        Cache::forever($this->notificationCacheKey($request), $state);

        return back()->with('success', 'Notifikasi dihapus. Riwayat pengaduan tetap tersimpan.');
    }

    public function destroyReadNotifications(Request $request)
    {
        $state = $this->notificationState($request);
        $laporans = Laporan::where('id_pengguna', $request->user()->id)
            ->whereNotNull('riwayat_status')
            ->get(['id_laporan', 'riwayat_status']);

        foreach ($laporans as $laporan) {
            foreach ($laporan->riwayat_status ?? [] as $eventIndex => $entry) {
                $notificationKey = $this->notificationEventKey($laporan->id_laporan, $eventIndex);
                if (isset($state['read'][$notificationKey])) {
                    $state['deleted'][$notificationKey] = true;
                }
            }
        }

        Cache::forever($this->notificationCacheKey($request), $state);

        return back()->with('success', 'Semua notifikasi yang sudah dibaca telah dihapus. Riwayat pengaduan tetap tersimpan.');
    }

    public function edit(Request $request, int $id)
    {
        $laporan = $this->ownedLaporan($request, $id);
        if ($laporan->status_penanganan !== 'Menunggu Verifikasi') {
            return redirect()->route('pelapor.laporan.show', $laporan->id_laporan)
                ->with('error', 'Pengaduan hanya dapat diedit saat Menunggu Verifikasi.');
        }

        return view('pelapor.laporan.form', [
            'laporan' => $laporan,
            'kategori' => self::KATEGORI,
            'peranPelapor' => self::PERAN_PELAPOR,
            'fotoTersimpan' => $this->photoReferences($laporan),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $laporan = $this->ownedLaporan($request, $id);
        if ($laporan->status_penanganan !== 'Menunggu Verifikasi') {
            return redirect()->route('pelapor.laporan.show', $laporan->id_laporan)
                ->with('error', 'Pengaduan hanya dapat diedit saat Menunggu Verifikasi.');
        }

        $validated = $this->validateReport($request, $laporan);
        $photos = $validated['foto'] ?? [];
        unset($validated['foto']);
        
        $laporan->update($validated);
        
        if (!empty($photos)) {
            $this->replacePhotos($laporan, $photos);
        }

        return redirect()->route('pelapor.laporan.show', $laporan->id_laporan)
            ->with('success', 'Pengaduan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->ownedLaporan($request, $id);

        return redirect()->route('pelapor.laporan.index')
            ->with('error', 'Pengaduan yang sudah dikirim tidak dapat dihapus agar riwayat penanganan tetap utuh.');
    }

    public function information()
    {
        // Diubah menggunakan $informasis agar sinkron dengan view pelapor.information
        $informasis = Informasi::where('status', 'published')->latest()->get();

        return view('pelapor.information', compact('informasis'));
    }

    public function emergency()
    {
        return view('pelapor.emergency');
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $tanggalLahir = null;

        if ($user->tanggal_lahir) {
            try {
                $tanggalLahir = preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $user->tanggal_lahir)
                    ? Carbon::createFromFormat('d/m/Y', $user->tanggal_lahir)->format('Y-m-d')
                    : Carbon::parse($user->tanggal_lahir)->format('Y-m-d');
            } catch (\Throwable) {
                $tanggalLahir = null;
            }
        }

        return view('pelapor.profile', compact('user', 'tanggalLahir'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M} ]+$/u'],
            'no_hp' => ['required', 'regex:/^08[0-9]{8,11}$/', Rule::unique('user', 'no_hp')->ignore($user->id)],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d', 'before:today'],
            'alamat' => ['required', 'string', 'max:2000'],
            'password_sekarang' => [Rule::requiredIf($request->filled('password_baru')), 'nullable', 'string'],
            'password_baru' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).+$/', 'confirmed'],
        ], [
            'name.regex' => 'Nama lengkap hanya boleh berisi huruf dan spasi.',
            'no_hp.regex' => 'Nomor HP harus diawali 08 dan berisi 10 sampai 13 digit.',
            'no_hp.unique' => 'Nomor HP ini sudah digunakan akun lain.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'password_sekarang.required' => 'Kata sandi saat ini wajib diisi untuk mengubah kata sandi.',
            'password_baru.regex' => 'Kata sandi baru harus memiliki huruf besar, huruf kecil, angka, dan simbol.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if ($request->filled('password_baru') && !Hash::check($request->input('password_sekarang'), $user->password)) {
            throw ValidationException::withMessages(['password_sekarang' => 'Kata sandi saat ini tidak sesuai.']);
        }

        $user->name = $validated['name'];
        $user->no_hp = $validated['no_hp'];
        $user->tanggal_lahir = $validated['tanggal_lahir'];
        $user->alamat = $validated['alamat'];
        if ($request->filled('password_baru')) {
            $user->password = $validated['password_baru'];
        }
        $user->save();

        return redirect()->route('pelapor.profile')->with('success', 'Profil berhasil diperbarui.');
    }

    private function ownedLaporan(Request $request, int $id): Laporan
    {
        return Laporan::where('id_pengguna', $request->user()->id)
            ->where('id_laporan', $id)
            ->firstOrFail();
    }

    private function ownedNotificationEntry(Request $request, int $id, int $eventIndex): array
    {
        $laporan = $this->ownedLaporan($request, $id);
        $entry = $laporan->riwayat_status[$eventIndex] ?? null;
        abort_unless(is_array($entry), 404);

        return [$laporan, $entry];
    }

    private function notificationCacheKey(Request $request): string
    {
        return 'pelapor-notifications:' . $request->user()->id;
    }

    private function notificationEventKey(int $laporanId, int $eventIndex): string
    {
        return $laporanId . ':' . $eventIndex;
    }

    private function notificationState(Request $request): array
    {
        $state = Cache::get($this->notificationCacheKey($request), []);

        return [
            'read' => is_array($state['read'] ?? null) ? $state['read'] : [],
            'deleted' => is_array($state['deleted'] ?? null) ? $state['deleted'] : [],
        ];
    }

    private function reportRules(): array
    {
        return [
            'kategori' => ['required', Rule::in(self::KATEGORI)],
            'sebagai' => ['required', Rule::in(self::PERAN_PELAPOR)],
            'lokasi_kejadian' => ['required', 'string', 'min:4', 'max:50'],
            'tanggal_kejadian' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'kronologi' => ['required', 'string', 'min:100', 'max:5000', 'regex:/\p{L}/u'],
        ];
    }

    private function validateReport(Request $request, ?Laporan $laporan = null): array
    {
        $existingCount = $laporan ? count($this->photoReferences($laporan)) : 0;
        $rules = $this->reportRules();
        
        $rules['foto'] = $laporan && $existingCount > 0
            ? ['nullable', 'array', 'max:3']
            : ['required', 'array', 'min:1', 'max:3'];
            
        $rules['foto.*'] = ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'];
        
        $validated = $request->validate($rules, [
            'lokasi_kejadian.min' => 'Lokasi kejadian minimal 4 karakter.',
            'lokasi_kejadian.max' => 'Lokasi kejadian maksimal 50 karakter.',
            'kronologi.min' => 'Penjelasan kejadian minimal 100 karakter.',
            'kronologi.max' => 'Penjelasan kejadian maksimal 5.000 karakter.',
            'kronologi.regex' => 'Penjelasan kejadian harus mengandung huruf.',
            'foto.required' => 'Unggah minimal 1 foto bukti.',
            'foto.min' => 'Unggah minimal 1 foto bukti.',
            'foto.max' => 'Maksimal 3 foto bukti.',
            'foto.*.image' => 'Bukti harus berupa file gambar.',
            'foto.*.mimes' => 'Format foto yang diperbolehkan hanya JPG, JPEG, dan PNG.',
            'foto.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ]);

        if (!$laporan && count($validated['foto'] ?? []) > 3) {
            throw ValidationException::withMessages(['foto' => 'Maksimal 3 foto bukti untuk satu pengaduan.']);
        }

        return $validated;
    }

    private function photoReferences(Laporan $laporan): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $laporan->bukti_lampiran))));
    }

    private function replacePhotos(Laporan $laporan, array $photos): void
    {
        if (empty($photos)) {
            return;
        }

        $directory = 'laporan/' . $laporan->id_laporan;

        if (Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->deleteDirectory($directory);
        }

        Storage::disk('local')->makeDirectory($directory);
        $newReferences = [];

        foreach ($photos as $photo) {
            $extension = strtolower($photo->getClientOriginalExtension());
            $filename = Str::random(16) . '.' . $extension;
            $newReferences[] = $photo->storeAs($directory, $filename, 'local');
        }

        $value = implode(', ', $newReferences);
        $laporan->bukti_lampiran = $value;
        $laporan->save();
    }

    private function appendPhotos(Laporan $laporan, array $photos): void
    {
        if (!$photos) {
            return;
        }

        $references = $this->photoReferences($laporan);
        $directory = 'laporan/' . $laporan->id_laporan;
        Storage::disk('local')->makeDirectory($directory);
        foreach ($photos as $photo) {
            $extension = strtolower($photo->getClientOriginalExtension());
            $filename = Str::random(16) . '.' . $extension;
            $references[] = $photo->storeAs($directory, $filename, 'local');
        }

        $value = implode(', ', $references);
        $laporan->bukti_lampiran = $value;
        $laporan->save();
    }

    private function newReportNumber(): string
    {
        $today = now()->format('Ymd');
        $countToday = Laporan::whereDate('created_at', today())->count();
        $sequence = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        return 'LAP-' . $today . '-' . $sequence;
    }
}
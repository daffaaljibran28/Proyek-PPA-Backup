<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PelaporAuthController;
use App\Http\Controllers\PelaporController;
use App\Http\Controllers\AdminInformasiController;
use App\Http\Controllers\AdminPenggunaController;
use App\Http\Middleware\EnsurePelapor;
use App\Http\Middleware\EnsureAdmin;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Hash;

// ========================================================
// Rute Utama / Beranda (Otomatis Deteksi Role Pengguna)
// ========================================================
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    // Jika admin, arahkan ke dashboard admin
    if (auth()->user()->role === 'admin' || (auth()->user()->is_admin ?? false)) {
        return redirect()->route('dashboard');
    }
    
    // Jika pelapor, arahkan ke beranda pelapor
    return redirect()->route('pelapor.home');
});

// ========================================================
// Autentikasi Pelapor (Login, Register, Lupa Password)
// ========================================================
Route::prefix('pelapor')->name('pelapor.')->group(function () {
    Route::get('/login', [PelaporAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PelaporAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    
    Route::get('/register', [PelaporAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [PelaporAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');

    // Rute Lupa / Ganti Password Langsung
    Route::get('/lupa-password', [PelaporAuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/lupa-password', [PelaporAuthController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update');
});

// ========================================================
// Dashboard & Fitur Utama Pelapor (Wajib Login Pelapor)
// ========================================================
Route::prefix('pelapor')->name('pelapor.')->middleware(EnsurePelapor::class)->group(function () {
    Route::get('/home', [PelaporController::class, 'dashboard'])->name('home');
    Route::post('/logout', [PelaporAuthController::class, 'logout'])->name('logout');
    Route::get('/pengaduan', [PelaporController::class, 'index'])->name('laporan.index');
    Route::get('/pengaduan/buat', [PelaporController::class, 'create'])->name('laporan.create');
    Route::post('/pengaduan', [PelaporController::class, 'store'])->name('laporan.store');
    Route::get('/pengaduan/{id}', [PelaporController::class, 'show'])->name('laporan.show');
    Route::get('/pengaduan/{id}/bukti/{index}', [PelaporController::class, 'evidence'])->whereNumber('index')->name('laporan.evidence');
    Route::post('/notifikasi/{id}/{eventIndex}/baca', [PelaporController::class, 'markNotificationRead'])->whereNumber('eventIndex')->name('notifications.read');
    Route::delete('/notifikasi/{id}/{eventIndex}', [PelaporController::class, 'destroyNotification'])->whereNumber('eventIndex')->name('notifications.destroy');
    Route::delete('/notifikasi-terbaca', [PelaporController::class, 'destroyReadNotifications'])->name('notifications.destroy-read');
    Route::get('/pengaduan/{id}/edit', [PelaporController::class, 'edit'])->name('laporan.edit');
    Route::put('/pengaduan/{id}', [PelaporController::class, 'update'])->name('laporan.update');
    Route::delete('/pengaduan/{id}', [PelaporController::class, 'destroy'])->name('laporan.destroy');
    Route::get('/emergency-call', [PelaporController::class, 'emergency'])->name('emergency');
    Route::get('/informasi-edukasi', [PelaporController::class, 'information'])->name('information');
    Route::get('/profil', [PelaporController::class, 'profile'])->name('profile');
    Route::put('/profil', [PelaporController::class, 'updateProfile'])->name('profile.update');
});

// ========================================================
// Autentikasi Admin (Login & Logout)
// ========================================================
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('logout');

// ========================================================
// Web Admin Dashboard & Manajemen (Wajib Login Admin)
// ========================================================
Route::middleware(['auth', EnsureAdmin::class])->group(function () {
    // Dashboard & Laporan
    Route::get('/dashboard-admin', [LaporanController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{id}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::get('/laporan/{id}/bukti/{index}', [LaporanController::class, 'evidence'])->whereNumber('index')->name('laporan.evidence');
    Route::put('/laporan/{id}/status', [LaporanController::class, 'updateStatus'])->name('laporan.update-status');

    // Manajemen Informasi & Edukasi oleh Admin
    Route::get('/admin/informasi', [AdminInformasiController::class, 'index'])->name('admin.informasi.index');
    Route::get('/admin/informasi/buat', [AdminInformasiController::class, 'create'])->name('admin.informasi.create');
    Route::post('/admin/informasi', [AdminInformasiController::class, 'store'])->name('admin.informasi.store');
    Route::get('/admin/informasi/{id}/edit', [AdminInformasiController::class, 'edit'])->name('admin.informasi.edit');
    Route::put('/admin/informasi/{id}', [AdminInformasiController::class, 'update'])->name('admin.informasi.update');
    Route::delete('/admin/informasi/{id}', [AdminInformasiController::class, 'destroy'])->name('admin.informasi.destroy');

    // Manajemen Pengguna
    Route::get('/pengguna', function () {
        $penggunas = User::where('role', 'pengguna')->latest()->get();
        return view('pengguna.index', compact('penggunas'));
    })->name('pengguna.index');
    Route::patch('/pengguna/{id}/status', [AdminPenggunaController::class, 'toggleStatus'])
        ->whereNumber('id')
        ->name('pengguna.toggle-status');

    Route::get('/pengguna/login', function () {
        return view('pengguna.login');
    })->name('pengguna.login');

    Route::get('/pengguna/{id}', function ($id) {
        $user = User::findOrFail($id);
        $laporans = Laporan::where('id_pengguna', $id)->latest()->get();
        return view('pengguna.show', compact('user', 'laporans'));
    })->name('pengguna.show');

    // Pengaturan Akun Admin
    Route::get('/pengaturan', function () {
        $admin = auth()->user();
        return view('pengaturan.index', compact('admin'));
    })->name('pengaturan.index');

    Route::post('/pengaturan/profil', function (Request $request) {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user,email,' . auth()->id(),
        ], [
            'name.required'  => 'Nama admin wajib diisi.',
            'email.required' => 'Email admin wajib diisi.',
            'email.unique'   => 'Email sudah terdaftar untuk pengguna lain.',
        ]);

        /** @var User $admin */
        $admin = auth()->user();
        $admin->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success_profil', 'Profil admin berhasil diperbarui!');
    })->name('pengaturan.update-profil');

    Route::post('/pengaturan/password', function (Request $request) {
        $request->validate([
            'password_lama'       => 'required',
            'password_baru'       => 'required|string|min:6',
            'konfirmasi_password' => 'required|same:password_baru',
        ], [
            'password_lama.required'       => 'Kata sandi lama wajib diisi.',
            'password_baru.required'       => 'Kata sandi baru wajib diisi.',
            'password_baru.min'            => 'Kata sandi baru minimal 6 karakter.',
            'konfirmasi_password.required' => 'Konfirmasi kata sandi wajib diisi.',
            'konfirmasi_password.same'     => 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru.',
        ]);

        /** @var User $admin */
        $admin = auth()->user();

        if (!Hash::check($request->password_lama, $admin->password)) {
            return back()->withErrors([
                'password_lama' => 'Kata sandi lama yang Anda masukkan tidak sesuai.',
            ]);
        }

        $admin->update([
            'password' => $request->password_baru,
        ]);

        return back()->with('success_password', 'Kata sandi berhasil diperbarui!');
    })->name('pengaturan.update-password');
});

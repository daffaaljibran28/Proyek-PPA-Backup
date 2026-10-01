<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PelaporAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && strtolower((string) Auth::user()->role) === 'pengguna') {
            return redirect()->route('pelapor.home');
        }

        return view('pelapor.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (!Auth::attempt($credentials + ['role' => 'pengguna'], $request->boolean('remember'))) {
            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => 'Email atau kata sandi tidak sesuai dengan akun pelapor.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('pelapor.home'));
    }

    public function showRegister()
    {
        return view('pelapor.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M} ]+$/u'],
            'nik' => ['required', 'digits:16', 'unique:user,nik'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d'],
            'alamat' => ['required', 'string', 'max:2000'],
            'email' => ['required', 'email', 'max:255', 'unique:user,email'],
            'no_hp' => ['required', 'regex:/^08[0-9]{8,11}$/', 'unique:user,no_hp'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).+$/', 'confirmed'],
        ], [
            'name.regex' => 'Nama lengkap hanya boleh berisi huruf dan spasi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'tanggal_lahir.date_format' => 'Tanggal lahir tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'no_hp.regex' => 'Nomor HP harus diawali 08 dan berisi 10 sampai 13 digit.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi harus memiliki huruf besar, huruf kecil, angka, dan simbol (!@#$%^&*).',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $validated['name'],
            'nik' => $validated['nik'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'role' => 'pengguna',
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('pelapor.login')->with('success', 'Pendaftaran berhasil. Silakan masuk dengan akun Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pelapor.login')->with('success', 'Anda telah keluar dari akun.');
    }
}
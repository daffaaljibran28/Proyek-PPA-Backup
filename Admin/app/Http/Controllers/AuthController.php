<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:user,nik',
            'no_hp' => 'required|string|min:10|max:20|unique:user,no_hp',
            'email' => 'required|string|email|max:255|unique:user,email',
            'password' => 'required|string|min:8',
            'tanggal_lahir' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:2000',
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'nik' => $validated['nik'],
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'role' => 'pengguna',
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'data' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->orWhere('no_hp', $request->email)
            ->first();

        if (
            ! $user
            || $user->isAdmin()
            || ! $user->is_active
            || ! Hash::check($request->password, $user->password)
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah!',
            ], 401);
        }

        $user->tokens()->where('name', 'flutter-app')->delete();
        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [...$user->toArray(), 'token' => $token],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil keluar.',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminPenggunaController extends Controller
{
    public function toggleStatus(int $id): RedirectResponse
    {
        $user = User::where('role', 'pengguna')->findOrFail($id);
        $willBeActive = ! $user->is_active;

        DB::transaction(function () use ($user, $willBeActive) {
            $user->update(['is_active' => $willBeActive]);

            if (! $willBeActive) {
                $user->tokens()->delete();
            }
        });

        return back()->with(
            'success',
            $willBeActive
                ? 'Akun pengguna berhasil diaktifkan kembali.'
                : 'Akun pengguna berhasil dinonaktifkan.'
        );
    }
}

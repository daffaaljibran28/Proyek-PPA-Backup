<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (! $user->isAdmin()) {
            if ($request->expectsJson()) {
                abort(403);
            }

            if (strtolower((string) $user->role) === 'pengguna') {
                return redirect()->route('pelapor.home')
                    ->with('error', 'Halaman tersebut khusus admin. Masuk dengan akun admin untuk melanjutkan.');
            }

            abort(403);
        }

        return $next($request);
    }
}
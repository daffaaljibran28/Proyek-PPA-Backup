<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePelapor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->guest(route('pelapor.login'));
        }

        if (strtolower((string) $request->user()->role) !== 'pengguna') {
            abort(403);
        }

        return $next($request);
    }
}
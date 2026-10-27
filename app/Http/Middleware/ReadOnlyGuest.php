<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun tamu hanya boleh melihat. Semua permintaan yang mengubah data ditolak,
 * kecuali keluar dan berpindah organisasi.
 */
class ReadOnlyGuest
{
    private const ALLOWED = ['logout', 'organizations.switch'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isGuest() && ! $request->isMethodSafe() && ! $request->routeIs(...self::ALLOWED)) {
            return back()->with('error', 'Mode tamu hanya untuk melihat. Masuk dengan akun sendiri untuk mengubah data.');
        }

        return $next($request);
    }
}

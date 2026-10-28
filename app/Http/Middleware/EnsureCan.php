<?php

namespace App\Http\Middleware;

use App\Support\Access;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Hanya peran yang punya hak akses tertentu (lihat config/tadbira.php "roles"), mis. middleware('can.tadbira:settings'). */
class EnsureCan
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $access = Access::for($request->user());
        abort_unless($access->organization, 403, 'Akun Anda belum tergabung dalam organisasi mana pun.');
        abort_unless($access->can($ability), 403, 'Peran Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}

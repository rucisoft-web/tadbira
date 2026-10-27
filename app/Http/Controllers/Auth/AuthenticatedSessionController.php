<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\GuestDemo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'demos' => GuestDemo::cards(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Masuk dengan akun tamu bersama (hanya-baca), bila diaktifkan.
     */
    public function guest(Request $request): RedirectResponse
    {
        $user = User::guestAccount();
        abort_if($user === null, 404);

        // Pengguna yang sudah masuk dengan akunnya sendiri tidak diganti menjadi tamu.
        if ($request->user() && ! $request->user()->isGuest()) {
            return redirect()->route('dashboard');
        }

        if (! $request->user()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        $org = $user->organizations()->where('organizations.ulid', $request->query('organisasi'))->first();
        if ($org) {
            $request->session()->put('guest_organization_id', $org->id);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

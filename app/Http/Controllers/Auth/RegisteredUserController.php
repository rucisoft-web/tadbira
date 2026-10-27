<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', ['sectors' => config('tadbira.sectors')]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'organization_name' => 'required|string|max:150',
            'sector' => ['required', Rule::in(array_keys(config('tadbira.sectors')))],
        ]);

        // Pendaftar pertama sebuah organisasi menjadi admin dan langsung
        // mendapat tahun anggaran berjalan.
        $user = DB::transaction(function () use ($request) {
            $organization = Organization::create([
                'name' => $request->organization_name,
                'sector' => $request->sector,
            ]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'current_organization_id' => $organization->id,
            ]);
            $organization->users()->attach($user->id, ['role' => 'admin']);

            $year = (int) now()->format('Y');
            $organization->fiscalYears()->create([
                'year' => $year,
                'code' => in_array($request->sector, ['bumn', 'bumd'], true) ? "RKAP {$year}" : "TA {$year}",
                'start_date' => "{$year}-01-01",
                'end_date' => "{$year}-12-31",
                'status' => 'active',
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

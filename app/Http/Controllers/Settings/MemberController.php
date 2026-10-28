<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Pengguna organisasi: tambah, ubah peran dan unit, nonaktifkan, setel ulang kata sandi, keluarkan. */
class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $org = $this->organization($request);
        $units = $org->units()->get(['id', 'ulid', 'name', 'full_name']);
        $unitNames = $units->pluck('name', 'id');
        $leads = $org->projects()->where('is_template', false)->whereNotNull('leader_id')
            ->selectRaw('leader_id, count(*) as n')->groupBy('leader_id')->pluck('n', 'leader_id');

        return Inertia::render('Settings/Members', [
            'members' => $this->members($org)->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->pivot->role,
                'role_label' => Access::roleLabel($u->pivot->role),
                'unit_id' => $u->pivot->unit_id,
                'unit' => $unitNames[$u->pivot->unit_id] ?? null,
                'is_active' => (bool) $u->pivot->is_active,
                'last_login_at' => $u->last_login_at?->toIso8601String(),
                'projects' => (int) ($leads[$u->id] ?? 0),
                'is_self' => $u->id === $request->user()->id,
            ])->values(),
            'roles' => collect(config('tadbira.roles'))->map(fn ($r, $k) => ['key' => $k, 'label' => $r['label'], 'description' => $r['description']])->values(),
            'units' => $units->map->only(['id', 'name', 'full_name'])->values(),
            'newPassword' => $request->session()->get('new_password'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::notIn([Str::lower((string) config('tadbira.guest.email'))])],
            'role' => ['required', Rule::in(array_keys(config('tadbira.roles')))],
            'unit_id' => ['nullable', Rule::exists('units', 'id')->where('organization_id', $org->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
        ], ['email.not_in' => 'Surel ini dipakai akun tamu.']);

        $user = User::where('email', $data['email'])->first();
        if ($user && $org->users()->whereKey($user->id)->exists()) {
            return back()->withErrors(['email' => 'Pengguna ini sudah terdaftar di organisasi.']);
        }

        $password = null;
        DB::transaction(function () use (&$user, &$password, $data, $org) {
            if (! $user) {
                $password = $data['password'] ?? Str::password(12, symbols: false);
                $user = User::create([
                    'name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($password),
                    'current_organization_id' => $org->id,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            $org->users()->attach($user->id, ['role' => $data['role'], 'unit_id' => $data['unit_id'] ?? null, 'is_active' => true]);
        });

        $message = $password
            ? "Akun {$user->email} dibuat. Berikan kata sandi awal di bawah kepada yang bersangkutan."
            : "{$user->name} sudah punya akun Tadbira dan kini ditambahkan ke organisasi ini dengan kata sandinya sendiri.";

        return back()->with('success', $message)->with('new_password', $password ? ['email' => $user->email, 'password' => $password] : null);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $org = $this->organization($request);
        $member = $this->member($org, $user);
        $data = $request->validate([
            'role' => ['sometimes', Rule::in(array_keys(config('tadbira.roles')))],
            'unit_id' => ['sometimes', 'nullable', Rule::exists('units', 'id')->where('organization_id', $org->id)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $losesAdmin = $member->pivot->role === 'admin' && (
            (isset($data['role']) && $data['role'] !== 'admin') || (isset($data['is_active']) && ! $data['is_active'])
        );
        if ($losesAdmin && $user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menurunkan peran atau menonaktifkan akun Anda sendiri. Minta admin lain melakukannya.');
        }
        if ($losesAdmin && $this->activeAdmins($org) <= 1) {
            return back()->with('error', 'Organisasi harus punya minimal satu admin aktif.');
        }

        $org->users()->updateExistingPivot($user->id, $data);

        return back()->with('success', isset($data['is_active'])
            ? ($data['is_active'] ? "{$user->name} diaktifkan kembali." : "{$user->name} dinonaktifkan dan tidak bisa masuk ke organisasi ini.")
            : "Peran {$user->name} diperbarui.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $org = $this->organization($request);
        $this->member($org, $user);
        abort_if($user->id === $request->user()->id, 422, 'Ganti kata sandi Anda sendiri lewat menu Profil.');
        // Akun yang juga tergabung di organisasi lain dipakai bersama; kata sandinya hanya boleh diganti pemiliknya.
        if ($user->organizations()->count() > 1) {
            return back()->with('error', "{$user->name} juga tergabung di organisasi lain, jadi kata sandinya hanya bisa diganti sendiri lewat \"Lupa kata sandi\" atau oleh pengelola server.");
        }

        $password = Str::password(12, symbols: false);
        $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return back()->with('success', "Kata sandi {$user->name} disetel ulang.")
            ->with('new_password', ['email' => $user->email, 'password' => $password]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $org = $this->organization($request);
        $member = $this->member($org, $user);
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa mengeluarkan diri sendiri.');
        }
        if ($member->pivot->role === 'admin' && $member->pivot->is_active && $this->activeAdmins($org) <= 1) {
            return back()->with('error', 'Organisasi harus punya minimal satu admin aktif.');
        }

        $org->users()->detach($user->id);
        if ((int) $user->current_organization_id === $org->id) {
            $user->update(['current_organization_id' => null]);
        }

        return back()->with('success', "{$user->name} dikeluarkan dari organisasi. Kegiatan dan riwayat yang pernah ia buat tetap tersimpan.");
    }

    private function members(Organization $org)
    {
        return $org->users()->where('users.email', '!=', config('tadbira.guest.email'))
            ->orderByDesc('organization_user.is_active')->orderBy('users.name')->get();
    }

    private function member(Organization $org, User $user): User
    {
        abort_if($user->isGuest(), 404);

        return $org->users()->whereKey($user->id)->firstOrFail();
    }

    private function activeAdmins(Organization $org): int
    {
        return $org->users()->wherePivot('role', 'admin')->wherePivot('is_active', true)->count();
    }
}

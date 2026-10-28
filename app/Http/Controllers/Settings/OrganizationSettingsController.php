<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\Access;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $org = $this->organization($request);

        return Inertia::render('Settings/Organization', [
            'profile' => $org->only(['name', 'short_name', 'code', 'sector', 'timezone']),
            'sectors' => config('tadbira.sectors'),
            'timezones' => ['Asia/Jakarta' => 'WIB (Asia/Jakarta)', 'Asia/Makassar' => 'WITA (Asia/Makassar)', 'Asia/Jayapura' => 'WIT (Asia/Jayapura)'],
            'stats' => [
                'members' => $org->users()->wherePivot('is_active', true)->where('users.email', '!=', config('tadbira.guest.email'))->count(),
                'units' => $org->units()->count(),
                'fiscal_years' => $org->fiscalYears()->count(),
                'projects' => $org->projects()->where('is_template', false)->count(),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:30'],
            'sector' => ['required', Rule::in(array_keys(config('tadbira.sectors')))],
            'timezone' => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
        ]);
        $org->update($data);

        return back()->with('success', 'Profil organisasi disimpan.');
    }

    /** Daftar peran dan hak aksesnya (hanya-baca). */
    public function roles(Request $request): Response
    {
        $org = $this->organization($request);
        $counts = $org->users()->where('users.email', '!=', config('tadbira.guest.email'))->get()
            ->countBy(fn ($u) => Access::roleLabel($u->pivot->role));

        return Inertia::render('Settings/Roles', [
            'roles' => collect(config('tadbira.roles'))->map(fn ($r, $key) => [
                'key' => $key,
                'label' => $r['label'],
                'description' => $r['description'],
                'can' => collect(config('tadbira.abilities'))->keys()->mapWithKeys(fn ($a) => [$a => $a === 'view' ? true : ($r['can'][$a] ?? false)]),
                'count' => $counts[$r['label']] ?? 0,
            ])->values(),
            'abilities' => config('tadbira.abilities'),
        ]);
    }
}

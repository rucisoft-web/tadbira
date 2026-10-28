<?php

namespace App\Support;

use App\Models\User;

/**
 * Kartu organisasi demo yang bisa dibuka dengan akun tamu.
 */
class GuestDemo
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function cards(): array
    {
        $guest = User::guestAccount();
        if ($guest === null) {
            return [];
        }

        return $guest->organizations()->orderBy('organizations.id')->get()->map(function ($org) {
            $fy = $org->fiscalYears()->where('year', now()->year)->first() ?? $org->fiscalYears()->first();
            $projects = $fy ? $org->projects()->where('fiscal_year_id', $fy->id) : null;

            return [
                'ulid' => $org->ulid,
                'name' => $org->name,
                'short_name' => $org->short_name ?: $org->name,
                'sector' => config("tadbira.sectors.{$org->sector}"),
                'year' => $fy?->year,
                'projects' => $projects ? $projects->count() : 0,
                'budget' => $projects ? (float) $projects->sum('budget_amount') : 0,
                'project_term' => $org->terminology()->get('project'),
            ];
        })->all();
    }
}

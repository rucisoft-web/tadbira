<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Organization;
use App\Models\Project;
use App\Support\Access;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function organization(Request $request): Organization
    {
        $org = $request->user()->activeOrganization();
        abort_unless($org, 403, 'Akun Anda belum tergabung dalam organisasi mana pun.');

        return $org;
    }

    protected function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->organization_id === $this->organization($request)->id, 404);
    }

    protected function access(Request $request): Access
    {
        return Access::for($request->user(), $this->organization($request));
    }

    /** Pastikan peran pengguna boleh melakukan $ability pada kegiatan ini. */
    protected function authorizeAbility(Request $request, string $ability, Project $project): void
    {
        $this->authorizeProject($request, $project);
        abort_unless($this->access($request)->canOn($ability, $project), 403, 'Peran Anda tidak memiliki akses untuk mengubah kegiatan ini.');
    }

    /** Tahun anggaran terpilih (?ta=ulid), atau yang berjalan, atau yang terbaru. */
    protected function fiscalYear(Request $request, Organization $org): ?FiscalYear
    {
        if ($request->filled('ta')) {
            return $org->fiscalYears()->where('ulid', $request->string('ta'))->firstOrFail();
        }

        return $org->fiscalYears()->where('year', now()->year)->first()
            ?? $org->fiscalYears()->where('status', 'active')->first()
            ?? $org->fiscalYears()->first();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Organization;
use App\Models\Project;
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

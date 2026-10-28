<?php

namespace App\Http\Controllers\Control;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\BaselineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BaselineController extends Controller
{
    /** Baseline pertama cukup hak ubah kegiatan; menetapkan ulang butuh hak menyetujui perubahan. */
    public function store(Request $request, Project $project, BaselineService $baseline): RedirectResponse
    {
        $this->authorizeAbility($request, $project->hasBaseline() ? 'approve_change' : 'edit_project', $project);
        $data = $request->validate(['reason' => [$project->hasBaseline() ? 'required' : 'nullable', 'string', 'max:300']]);
        abort_unless($project->computed_finish_date, 422, 'Jadwal kegiatan belum terbentuk.');

        $baseline->capture($project, $request->user(), $data['reason'] ?? null);

        return back()->with('success', $project->baseline_number === 1 ? 'Baseline ditetapkan.' : "Baseline ke-{$project->baseline_number} ditetapkan.");
    }
}

<?php

namespace App\Http\Controllers\Control;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Project;
use App\Services\ActivityLogger;
use App\Services\BaselineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Kendali perubahan: siapa pun yang boleh mengubah kegiatan dapat mengajukan; pimpinan atau
 * perencana memutuskan. Setelah disetujui, baseline dapat ditetapkan ulang sekaligus.
 */
class ChangeRequestController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeAbility($request, 'edit_project', $project);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:3000'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'schedule_impact_days' => ['nullable', 'integer', 'between:-3650,3650'],
            'cost_impact' => ['nullable', 'numeric', 'between:-1000000000000,1000000000000'],
            'scope_impact' => ['nullable', 'string', 'max:2000'],
        ]);

        $cr = DB::transaction(fn () => $project->changeRequests()->create([
            ...$data,
            'schedule_impact_days' => (int) ($data['schedule_impact_days'] ?? 0),
            'cost_impact' => (float) ($data['cost_impact'] ?? 0),
            'number' => (int) $project->changeRequests()->lockForUpdate()->max('number') + 1,
            'requested_by' => $request->user()->id,
        ]));
        ActivityLogger::log($project, $cr, 'created', "{$request->user()->name} mengajukan perubahan {$cr->code()}: {$cr->title}");

        return back()->with('success', "Permintaan perubahan {$cr->code()} diajukan.");
    }

    public function decide(Request $request, ChangeRequest $changeRequest, BaselineService $baseline): RedirectResponse
    {
        $project = $changeRequest->project;
        $this->authorizeAbility($request, 'approve_change', $project);
        abort_unless($changeRequest->status === 'submitted', 422, 'Permintaan ini sudah diputuskan.');
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'decision_note' => [Rule::requiredIf($request->input('decision') === 'rejected'), 'nullable', 'string', 'max:2000'],
            'rebaseline' => ['boolean'],
        ]);

        DB::transaction(function () use ($request, $changeRequest, $project, $data, $baseline) {
            $rebaseline = $data['decision'] === 'approved' && ($data['rebaseline'] ?? false) && $project->computed_finish_date;
            $changeRequest->update([
                'status' => $data['decision'],
                'decision_note' => $data['decision_note'] ?? null,
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
                'rebaselined' => (bool) $rebaseline,
            ]);
            $verb = $data['decision'] === 'approved' ? 'menyetujui' : 'menolak';
            ActivityLogger::log($project, $changeRequest, $data['decision'], "{$request->user()->name} {$verb} perubahan {$changeRequest->code()}: {$changeRequest->title}");
            if ($rebaseline) {
                $baseline->capture($project, $request->user(), "perubahan {$changeRequest->code()} disetujui");
            }
        });

        return back()->with('success', "Perubahan {$changeRequest->code()} ".($data['decision'] === 'approved' ? 'disetujui.' : 'ditolak.'));
    }

    public function withdraw(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $this->authorizeAbility($request, 'edit_project', $changeRequest->project);
        abort_unless($changeRequest->status === 'submitted', 422, 'Hanya permintaan yang belum diputuskan yang dapat ditarik.');
        $changeRequest->update(['status' => 'withdrawn']);
        ActivityLogger::log($changeRequest->project, $changeRequest, 'withdrawn', "{$request->user()->name} menarik perubahan {$changeRequest->code()}");

        return back();
    }
}

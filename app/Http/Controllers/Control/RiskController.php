<?php

namespace App\Http\Controllers\Control;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiskController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeAbility($request, 'edit_project', $project);
        $risk = $project->risks()->create($this->validated($request, $project) + ['created_by' => $request->user()->id]);
        ActivityLogger::log($project, $risk, 'created', "{$request->user()->name} mencatat ".($risk->type === 'issue' ? 'isu' : 'risiko')." \"{$risk->title}\"");

        return back();
    }

    public function update(Request $request, ProjectRisk $risk): RedirectResponse
    {
        $this->authorizeAbility($request, 'edit_project', $risk->project);
        $risk->fill($this->validated($request, $risk->project, partial: true));
        if ($risk->isDirty('status')) {
            $risk->closed_at = $risk->status === 'closed' ? now() : null;
        }
        $changes = $risk->getDirty();
        $risk->save();
        if ($changes) {
            ActivityLogger::log($risk->project, $risk, 'updated', "{$request->user()->name} memperbarui ".($risk->type === 'issue' ? 'isu' : 'risiko')." \"{$risk->title}\"", $changes);
        }

        return back();
    }

    public function destroy(Request $request, ProjectRisk $risk): RedirectResponse
    {
        $this->authorizeAbility($request, 'edit_project', $risk->project);
        ActivityLogger::log($risk->project, $risk, 'deleted', "{$request->user()->name} menghapus \"{$risk->title}\" dari register risiko");
        $risk->delete();

        return back();
    }

    private function validated(Request $request, Project $project, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';
        $data = $request->validate([
            'type' => [$req, Rule::in(['risk', 'issue'])],
            'title' => [$req, 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', Rule::in(ProjectRisk::CATEGORIES)],
            'probability' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'impact' => [$req, 'integer', 'between:1,5'],
            'strategy' => ['sometimes', 'nullable', Rule::in(array_keys(ProjectRisk::STRATEGIES))],
            'response' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'owner_id' => ['sometimes', 'nullable', 'integer'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(array_keys(ProjectRisk::STATUSES))],
            'task' => ['sometimes', 'nullable', 'string'],
        ]);

        if (! empty($data['owner_id'])) {
            abort_unless($project->organization->users()->whereKey($data['owner_id'])->exists(), 422, 'Pemilik risiko bukan anggota organisasi.');
        }
        if (array_key_exists('task', $data)) {
            $data['task_id'] = $data['task'] ? $project->tasks()->where('ulid', $data['task'])->value('id') : null;
            unset($data['task']);
        }
        if (($data['type'] ?? null) === 'risk' && empty($data['probability']) && ! $partial) {
            $data['probability'] = 3;
        }

        return $data;
    }
}

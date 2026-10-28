<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    /** Pratinjau tanggal selesai (atau mulai) tanpa menyimpan. */
    public function preview(Request $request, Project $template, TemplateService $templates): JsonResponse
    {
        $this->authorizeProject($request, $template);
        abort_unless($template->is_template, 404);

        $data = $request->validate([
            'direction' => ['required', Rule::in(['forward', 'backward'])],
            'date' => ['required', 'date'],
            'fiscal_year' => ['nullable', 'string'],
        ]);
        $fy = ! empty($data['fiscal_year'])
            ? $template->organization->fiscalYears()->where('ulid', $data['fiscal_year'])->first()
            : null;

        return response()->json($templates->preview($template, $data['direction'], $data['date'], $fy));
    }

    public function store(Request $request, Project $project, TemplateService $templates): RedirectResponse
    {
        $this->authorizeAbility($request, 'edit_project', $project);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'use_actual_durations' => ['boolean'],
        ]);

        $templates->saveAsTemplate($project, $data['name'], $data['use_actual_durations'] ?? false);

        return back()->with('success', "Templat \"{$data['name']}\" disimpan.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BudgetLine;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenditureController extends Controller
{
    public function store(Request $request, BudgetLine $line): RedirectResponse
    {
        $project = $line->project;
        $this->authorizeAbility($request, 'record_expenditure', $project);
        abort_if($project->fiscalYear?->isClosed(), 422, 'Tahun anggaran sudah ditutup.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['committed', 'invoiced', 'paid'])],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $line->expenditures()->create($data + ['recorded_by' => $request->user()->id]);
        ActivityLogger::log($project, $line, 'expenditure', sprintf('%s mencatat realisasi Rp%s pada %s', $request->user()->name, number_format($data['amount'], 0, ',', '.'), $line->description));

        return back()->with('success', 'Realisasi dicatat.');
    }
}

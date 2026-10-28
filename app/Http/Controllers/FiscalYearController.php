<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FiscalYearController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2000,2100', Rule::unique('fiscal_years')->where('organization_id', $org->id)],
            'ceiling_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $fy = $org->fiscalYears()->create([
            'year' => $data['year'],
            'code' => 'TA '.$data['year'],
            'start_date' => "{$data['year']}-01-01",
            'end_date' => "{$data['year']}-12-31",
            'status' => 'planning',
            'ceiling_amount' => $data['ceiling_amount'] ?? null,
        ]);

        if ($request->boolean('kembali')) {
            return back()->with('success', "{$fy->code} dibuat.");
        }

        return redirect()->route('dashboard', ['ta' => $fy->ulid])->with('success', "{$fy->code} dibuat.");
    }
}

<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FiscalYearSettingsController extends Controller
{
    public const STATUSES = ['planning' => 'Perencanaan', 'active' => 'Berjalan', 'closed' => 'Ditutup'];

    public function index(Request $request): Response
    {
        $org = $this->organization($request);

        return Inertia::render('Settings/FiscalYears', [
            'fiscalYears' => $org->fiscalYears()->withCount(['projects' => fn ($q) => $q->where('is_template', false)])->get()
                ->map(fn (FiscalYear $fy) => [
                    'ulid' => $fy->ulid, 'year' => $fy->year, 'code' => $fy->code, 'status' => $fy->status,
                    'start_date' => $fy->start_date?->toDateString(), 'end_date' => $fy->end_date?->toDateString(),
                    'ceiling_amount' => $fy->ceiling_amount !== null ? (float) $fy->ceiling_amount : null,
                    'notes' => $fy->notes, 'projects' => $fy->projects_count,
                ])->values(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, FiscalYear $fiscalYear): RedirectResponse
    {
        abort_unless($fiscalYear->organization_id === $this->organization($request)->id, 404);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'ceiling_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $fiscalYear->update($data);

        return back()->with('success', "{$fiscalYear->code} disimpan.");
    }

    public function destroy(Request $request, FiscalYear $fiscalYear): RedirectResponse
    {
        abort_unless($fiscalYear->organization_id === $this->organization($request)->id, 404);
        if ($fiscalYear->projects()->withTrashed()->exists()) {
            return back()->with('error', "{$fiscalYear->code} sudah berisi kegiatan, jadi tidak bisa dihapus. Ubah statusnya menjadi Ditutup.");
        }
        $fiscalYear->delete();

        return back()->with('success', "{$fiscalYear->code} dihapus.");
    }
}

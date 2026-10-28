<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Organization;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Hari libur organisasi (di samping libur nasional) dan hari kerja pengganti; dipakai mesin penjadwalan. */
class HolidayController extends Controller
{
    public function index(Request $request): Response
    {
        $org = $this->organization($request);
        $year = (int) ($request->integer('tahun') ?: now()->year);

        return Inertia::render('Settings/Holidays', [
            'year' => $year,
            'years' => range(now()->year - 1, now()->year + 2),
            'holidays' => Holiday::query()
                ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $org->id))
                ->whereYear('date', $year)->orderBy('date')->get()
                ->map(fn (Holiday $h) => [
                    'id' => $h->id, 'date' => $h->date->toDateString(), 'name' => $h->name,
                    'is_working_day' => $h->is_working_day, 'national' => $h->organization_id === null,
                ])->values(),
        ]);
    }

    public function store(Request $request, ProjectScheduler $scheduler): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $request->validate([
            'date' => ['required', 'date', Rule::unique('holidays')->where('organization_id', $org->id)],
            'name' => ['required', 'string', 'max:150'],
            'is_working_day' => ['boolean'],
        ], ['date.unique' => 'Tanggal ini sudah ada di daftar.']);
        $holiday = $org->holidays()->create($data + ['is_working_day' => false]);
        $count = $this->reschedule($org, $holiday->date->toDateString(), $scheduler);

        return back()->with('success', "{$holiday->name} ditambahkan. {$count} kegiatan yang melewati tanggal itu dijadwalkan ulang.");
    }

    public function destroy(Request $request, Holiday $holiday, ProjectScheduler $scheduler): RedirectResponse
    {
        $org = $this->organization($request);
        abort_unless($holiday->organization_id === $org->id, 403, 'Libur nasional tidak bisa dihapus dari sini.');
        $date = $holiday->date->toDateString();
        $holiday->delete();
        $count = $this->reschedule($org, $date, $scheduler);

        return back()->with('success', "Hari libur dihapus. {$count} kegiatan dijadwalkan ulang.");
    }

    /** Jadwalkan ulang kegiatan berjalan yang rentang tanggalnya mencakup $date. */
    private function reschedule(Organization $org, string $date, ProjectScheduler $scheduler): int
    {
        $projects = $org->projects()->where('is_template', false)->whereNotIn('status', ['closed', 'cancelled'])
            ->whereDate('computed_start_date', '<=', $date)->whereDate('computed_finish_date', '>=', $date)->get();
        $projects->each(fn ($p) => $scheduler->reschedule($p));

        return $projects->count();
    }
}

<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Unit kerja. Nama unit sama dengan kategori kegiatan, jadi mengganti nama ikut memperbarui kegiatannya. */
class UnitController extends Controller
{
    public function index(Request $request): Response
    {
        $org = $this->organization($request);
        $projects = $org->projects()->where('is_template', false)->selectRaw('category, count(*) as n')->groupBy('category')->pluck('n', 'category');
        $members = DB::table('organization_user')->where('organization_id', $org->id)->whereNotNull('unit_id')
            ->selectRaw('unit_id, count(*) as n')->groupBy('unit_id')->pluck('n', 'unit_id');
        $units = $org->units()->get();
        $unlisted = $projects->keys()->filter()->diff($units->pluck('name'))->values();

        return Inertia::render('Settings/Units', [
            'units' => $units->map(fn (Unit $u) => [
                'ulid' => $u->ulid, 'name' => $u->name, 'full_name' => $u->full_name,
                'projects' => (int) ($projects[$u->name] ?? 0), 'members' => (int) ($members[$u->id] ?? 0),
            ])->values(),
            'unlisted' => $unlisted,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $this->validated($request, $org->id);
        $org->units()->create($data + ['position' => (int) $org->units()->max('position') + 1]);

        return back()->with('success', "Unit {$data['name']} ditambahkan.");
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $org = $this->organization($request);
        abort_unless($unit->organization_id === $org->id, 404);
        $data = $this->validated($request, $org->id, $unit->id);

        DB::transaction(function () use ($org, $unit, $data) {
            if ($data['name'] !== $unit->name) {
                $org->projects()->withTrashed()->where('category', $unit->name)->update(['category' => $data['name']]);
            }
            $unit->update($data);
        });

        return back()->with('success', "Unit {$data['name']} disimpan.");
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        $org = $this->organization($request);
        abort_unless($unit->organization_id === $org->id, 404);
        $count = $org->projects()->where('category', $unit->name)->count();
        if ($count) {
            return back()->with('error', "Unit {$unit->name} masih dipakai {$count} kegiatan. Pindahkan kegiatannya atau ganti nama unit ini.");
        }
        $unit->delete();

        return back()->with('success', "Unit {$unit->name} dihapus. Operator yang terkait kini tanpa unit.");
    }

    private function validated(Request $request, int $orgId, ?int $ignore = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('units')->where('organization_id', $orgId)->ignore($ignore)],
            'full_name' => ['nullable', 'string', 'max:200'],
        ], ['name.unique' => 'Nama unit ini sudah ada.']);
    }
}

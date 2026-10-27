<?php

namespace App\Services\Budget;

use App\Models\BudgetLine;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Task;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Support\Facades\DB;

/**
 * Menyimpan hasil BudgetSheetParser menjadi rincian anggaran dan SRK.
 *
 * Mode "per proyek": setiap baris pada tingkat pemisah (RO untuk POK,
 * Kelompok untuk RAB) menjadi satu proyek di tahun anggaran terpilih,
 * sehingga satu file POK langsung menghasilkan banyak proyek untuk dipantau.
 */
class BudgetImporter
{
    /** Tingkat anggaran yang menjadi unsur SRK, menurut format. */
    private const WBS_LEVELS = [
        'pok' => ['ro', 'komponen', 'subkomponen'],
        'rab' => ['kelompok', 'pos'],
    ];

    private const SPLIT_LEVEL = ['pok' => 'ro', 'rab' => 'kelompok'];

    public function __construct(private ProjectScheduler $scheduler)
    {
    }

    /**
     * Impor ke satu proyek yang sudah ada.
     */
    public function importIntoProject(Project $project, array $parsed, bool $buildWbs = true): int
    {
        return DB::transaction(function () use ($project, $parsed, $buildWbs) {
            $lineIds = $this->storeLines($project, $parsed['rows'], null);
            if ($buildWbs) {
                $this->buildWbs($project, $parsed['format'], $parsed['rows'], $lineIds);
            }
            $project->update(['budget_amount' => $this->rootTotal($parsed['rows'])]);
            $this->scheduler->reschedule($project);

            return count($lineIds);
        });
    }

    /**
     * Buat satu proyek untuk setiap baris tingkat pemisah (RO / Kelompok).
     *
     * @return list<Project>
     */
    public function importAsProjects(FiscalYear $fiscalYear, array $parsed, int $userId, bool $buildWbs = true): array
    {
        $split = self::SPLIT_LEVEL[$parsed['format']];
        $rows = $parsed['rows'];
        $byKey = [];
        foreach ($rows as $r) {
            $byKey[$r['key']] = $r;
        }

        $splitRows = array_values(array_filter($rows, fn ($r) => $r['level'] === $split));
        if ($splitRows === []) {
            // Tidak ada RO/Kelompok: seluruh isi menjadi satu proyek.
            $splitRows = array_values(array_filter($rows, fn ($r) => $r['parent_key'] === null));
        }

        return DB::transaction(function () use ($fiscalYear, $parsed, $rows, $byKey, $splitRows, $userId, $buildWbs) {
            $projects = [];
            foreach ($splitRows as $root) {
                $subtree = $this->subtree($rows, $root['key']);
                $project = Project::create([
                    'organization_id' => $fiscalYear->organization_id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'leader_id' => $userId,
                    'created_by' => $userId,
                    'code' => $this->fullCode($root, $byKey),
                    'name' => $root['description'],
                    'category' => $this->ancestorDescription($root, $byKey, 'kegiatan'),
                    'status' => 'planning',
                    'scheduling_direction' => 'forward',
                    'start_date' => $fiscalYear->start_date,
                    'budget_amount' => (float) $root['amount'],
                ]);
                $project->members()->attach($userId, ['role' => 'leader']);

                // Simpan baris pemisah sebagai akar agar kodenya tetap terlihat.
                $root['parent_key'] = null;
                $lineIds = $this->storeLines($project, array_merge([$root], $subtree), $fiscalYear->id);
                if ($buildWbs) {
                    $this->buildWbs($project, $parsed['format'], $subtree, $lineIds, skipSplitLevel: true);
                }
                $this->scheduler->reschedule($project);
                $projects[] = $project;
            }

            return $projects;
        });
    }

    /**
     * @return array<int, int> kunci baris → id budget_lines
     */
    private function storeLines(Project $project, array $rows, ?int $fiscalYearId): array
    {
        $ids = [];
        foreach ($rows as $i => $r) {
            $line = BudgetLine::create([
                'project_id' => $project->id,
                'fiscal_year_id' => $fiscalYearId ?? $project->fiscal_year_id,
                'parent_id' => $r['parent_key'] !== null ? ($ids[$r['parent_key']] ?? null) : null,
                'level' => $r['level'],
                'code' => $r['code'],
                'description' => mb_substr($r['description'], 0, 500),
                'volume' => $r['volume'],
                'unit' => $r['unit'],
                'unit_price' => $r['unit_price'],
                'amount' => $r['amount'] ?? 0,
                'funding_source' => $r['funding_source'],
                'position' => $i,
            ]);
            $ids[$r['key']] = $line->id;
        }

        return $ids;
    }

    /**
     * Bentuk SRK dari tingkat anggaran: tingkat teratas → tahap, berikutnya → paket kerja,
     * tingkat terbawah (atau yang tidak punya anak SRK) → aktivitas 5 hari kerja.
     */
    private function buildWbs(Project $project, string $format, array $rows, array $lineIds, bool $skipSplitLevel = false): void
    {
        $levels = self::WBS_LEVELS[$format];
        if ($skipSplitLevel) {
            $levels = array_values(array_diff($levels, [self::SPLIT_LEVEL[$format]]));
        }

        $wbsRows = array_values(array_filter($rows, fn ($r) => in_array($r['level'], $levels, true) && ! $r['is_group']));
        $byKey = [];
        foreach ($rows as $r) {
            $byKey[$r['key']] = $r;
        }

        // Induk SRK = leluhur terdekat yang juga unsur SRK.
        $wbsKeys = array_flip(array_column($wbsRows, 'key'));
        $wbsParent = [];
        foreach ($wbsRows as $r) {
            $p = $r['parent_key'];
            while ($p !== null && ! isset($wbsKeys[$p])) {
                $p = $byKey[$p]['parent_key'] ?? null;
            }
            $wbsParent[$r['key']] = $p;
        }
        $hasChildren = array_flip(array_filter($wbsParent, fn ($p) => $p !== null));

        $taskIds = [];
        $position = (int) $project->tasks()->max('position');
        foreach ($wbsRows as $r) {
            $parent = $wbsParent[$r['key']];
            $depth = 0;
            for ($p = $parent; $p !== null; $p = $wbsParent[$p]) {
                $depth++;
            }
            $kind = isset($hasChildren[$r['key']]) ? ($depth === 0 ? 'phase' : 'work_package') : 'activity';

            $task = Task::create([
                'project_id' => $project->id,
                'parent_id' => $parent !== null ? $taskIds[$parent] : null,
                'kind' => $kind,
                'position' => ++$position,
                'title' => trim(($r['code'] ? $r['code'].' ' : '').$r['description']),
                'duration_days' => $kind === 'activity' ? 5 : 1,
            ]);
            $taskIds[$r['key']] = $task->id;

            if (isset($lineIds[$r['key']])) {
                BudgetLine::whereKey($lineIds[$r['key']])->update(['task_id' => $task->id]);
            }
        }
    }

    private function subtree(array $rows, int $rootKey): array
    {
        $keys = [$rootKey => true];
        $out = [];
        foreach ($rows as $r) {
            if ($r['parent_key'] !== null && isset($keys[$r['parent_key']])) {
                $keys[$r['key']] = true;
                $out[] = $r;
            }
        }

        return $out;
    }

    /** Kode lengkap RO, mis. "4471.QDB.001". */
    private function fullCode(array $row, array $byKey): ?string
    {
        if ($row['code'] === null || str_contains($row['code'], '.')) {
            return $row['code'];
        }
        $parts = [$row['code']];
        for ($p = $row['parent_key']; $p !== null; $p = $byKey[$p]['parent_key']) {
            $parent = $byKey[$p];
            if (in_array($parent['level'], ['kegiatan', 'kro'], true) && $parent['code']) {
                array_unshift($parts, $parent['code']);
                if (str_contains($parent['code'], '.')) {
                    break;
                }
            }
        }

        return implode('.', array_unique($parts));
    }

    private function ancestorDescription(array $row, array $byKey, string $level): ?string
    {
        for ($p = $row['parent_key']; $p !== null; $p = $byKey[$p]['parent_key']) {
            if ($byKey[$p]['level'] === $level) {
                return mb_substr($byKey[$p]['description'], 0, 100);
            }
        }

        return null;
    }

    private function rootTotal(array $rows): float
    {
        return (float) array_sum(array_column(array_filter($rows, fn ($r) => $r['parent_key'] === null), 'amount'));
    }
}

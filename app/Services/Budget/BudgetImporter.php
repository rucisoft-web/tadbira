<?php

namespace App\Services\Budget;

use App\Models\BudgetLine;
use App\Models\Expenditure;
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
 *
 * Laporan realisasi (Laporan Ketersediaan Dana) juga membawa realisasi s.d. periode.
 * Mengunggah laporan bulan berikutnya memperbarui pagu dan realisasi proyek yang
 * kodenya sama, bukan membuat proyek baru.
 */
class BudgetImporter
{
    /** Tingkat anggaran yang menjadi unsur SRK, menurut format. */
    private const WBS_LEVELS = [
        'pok' => ['ro', 'komponen', 'subkomponen'],
        'rab' => ['kelompok', 'pos'],
    ];

    private const SPLIT_LEVEL = ['pok' => 'ro', 'rab' => 'kelompok'];

    /** Prefiks nomor bukti untuk realisasi yang berasal dari laporan. */
    public const REPORT_REFERENCE = 'LKD ';

    private ?array $period = null;

    public function __construct(private ProjectScheduler $scheduler)
    {
    }

    private function usePeriod(array $parsed): void
    {
        $this->period = ($parsed['source'] ?? null) === 'realisasi' ? [
            'date' => $parsed['meta']['period_end'] ?? now()->toDateString(),
            'label' => $parsed['meta']['period_label'] ?? now()->translatedFormat('F Y'),
        ] : null;
    }

    /**
     * Impor ke satu proyek yang sudah ada.
     */
    public function importIntoProject(Project $project, array $parsed, bool $buildWbs = true): int
    {
        $this->usePeriod($parsed);

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
     * Untuk laporan realisasi, proyek berkode sama di tahun anggaran ini diperbarui.
     *
     * @return array{created: list<Project>, updated: list<Project>}
     */
    public function importAsProjects(FiscalYear $fiscalYear, array $parsed, int $userId, bool $buildWbs = true): array
    {
        $this->usePeriod($parsed);
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
            $created = [];
            $updated = [];
            foreach ($splitRows as $root) {
                $subtree = $this->subtree($rows, $root['key']);
                $code = $this->fullCode($root, $byKey);
                $existing = $this->period !== null && $code !== null
                    ? Project::real()->where('fiscal_year_id', $fiscalYear->id)->where('code', $code)->first()
                    : null;
                if ($existing !== null) {
                    $root['parent_key'] = null;
                    $this->refreshLines($existing, array_merge([$root], $subtree), $fiscalYear->id);
                    $existing->update(['budget_amount' => (float) $root['amount']]);
                    $updated[] = $existing;

                    continue;
                }

                $project = Project::create([
                    'organization_id' => $fiscalYear->organization_id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'leader_id' => $userId,
                    'created_by' => $userId,
                    'code' => $code,
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
                $created[] = $project;
            }

            // Laporan realisasi mencakup seluruh DIPA satker: isi pagu tahun anggaran bila belum ada.
            if ($this->period !== null && ! (float) $fiscalYear->ceiling_amount) {
                $fiscalYear->update(['ceiling_amount' => $this->rootTotal($rows)]);
            }

            return ['created' => $created, 'updated' => $updated];
        });
    }

    /**
     * Perbarui baris anggaran proyek dari laporan periode berikutnya: pagu diganti,
     * baris baru ditambahkan, dan realisasi disesuaikan. Baris dicocokkan menurut
     * tingkat dan kodenya.
     */
    private function refreshLines(Project $project, array $rows, int $fiscalYearId): void
    {
        $existing = BudgetLine::where('project_id', $project->id)->get()
            ->keyBy(fn ($l) => $l->level.'|'.($l->code ?? $l->description));
        $leaf = $this->leafKeys($rows);
        $ids = [];
        $position = (int) BudgetLine::where('project_id', $project->id)->max('position');

        foreach ($rows as $r) {
            $line = $existing->get($r['level'].'|'.($r['code'] ?? $r['description']));
            if ($line !== null) {
                $line->update(['amount' => $r['amount'] ?? 0, 'description' => mb_substr($r['description'], 0, 500)]);
            } else {
                $line = BudgetLine::create([
                    'project_id' => $project->id,
                    'fiscal_year_id' => $fiscalYearId,
                    'parent_id' => $r['parent_key'] !== null ? ($ids[$r['parent_key']] ?? null) : null,
                    'level' => $r['level'],
                    'code' => $r['code'],
                    'description' => mb_substr($r['description'], 0, 500),
                    'amount' => $r['amount'] ?? 0,
                    'position' => ++$position,
                ]);
            }
            $ids[$r['key']] = $line->id;
            if (isset($leaf[$r['key']])) {
                $this->syncRealization($line->id, $r['realized'] ?? null);
            }
            if ($line->task_id !== null && $r['realized'] !== null) {
                Task::whereKey($line->task_id)->where('kind', 'activity')->update([
                    'percent_complete' => $pct = $this->realizedPercent($r),
                    'status' => $pct >= 100 ? 'completed' : ($pct > 0 ? 'in_progress' : 'not_started'),
                ]);
            }
        }
        $this->scheduler->reschedule($project);
    }

    /** Persentase realisasi anggaran sebagai perkiraan kemajuan (0–100). */
    private function realizedPercent(array $row): int
    {
        if ($this->period === null || empty($row['amount']) || $row['realized'] === null) {
            return 0;
        }

        return (int) max(0, min(100, round($row['realized'] / $row['amount'] * 100)));
    }

    /**
     * Catat realisasi kumulatif s.d. periode sebagai selisih terhadap laporan
     * sebelumnya, sehingga kurva S bulanan tetap benar bila laporan diunggah tiap bulan.
     * Mengunggah ulang periode yang sama menggantikan angkanya.
     */
    private function syncRealization(int $lineId, ?float $realized): void
    {
        if ($this->period === null || $realized === null) {
            return;
        }
        $fromReports = Expenditure::where('budget_line_id', $lineId)->where('reference', 'like', self::REPORT_REFERENCE.'%');
        (clone $fromReports)->where('transaction_date', '>=', $this->period['date'])->delete();
        $delta = round($realized - (float) (clone $fromReports)->sum('amount'), 2);

        if ($delta != 0.0) {
            Expenditure::create([
                'budget_line_id' => $lineId,
                'status' => 'paid',
                'amount' => $delta,
                'transaction_date' => $this->period['date'],
                'reference' => self::REPORT_REFERENCE.$this->period['label'],
            ]);
        }
    }

    /** Kunci baris yang tidak memiliki anak di dalam berkas. */
    private function leafKeys(array $rows): array
    {
        $parents = array_flip(array_filter(array_column($rows, 'parent_key'), fn ($k) => $k !== null));

        return array_flip(array_filter(array_column($rows, 'key'), fn ($k) => ! isset($parents[$k])));
    }

    /**
     * @return array<int, int> kunci baris → id budget_lines
     */
    private function storeLines(Project $project, array $rows, ?int $fiscalYearId): array
    {
        $ids = [];
        $leaf = $this->leafKeys($rows);
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
            if (isset($leaf[$r['key']])) {
                $this->syncRealization($line->id, $r['realized'] ?? null);
            }
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

        // Laporan realisasi memuat pagu setahun: aktivitas membentang sepanjang tahun anggaran
        // dan kemajuannya mengikuti persentase realisasi anggaran.
        $activityDays = 5;
        if ($this->period !== null && ($fy = $project->fiscalYear) !== null) {
            $calendar = $this->scheduler->calendarFor($project->organization_id);
            $activityDays = max(1, $calendar->workingDaysBetween($fy->start_date->subDay(), $fy->end_date));
        }

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
                'duration_days' => $kind === 'activity' ? $activityDays : 1,
                'percent_complete' => $pct = ($kind === 'activity' ? $this->realizedPercent($r) : 0),
                'status' => $pct >= 100 ? 'completed' : ($pct > 0 ? 'in_progress' : 'not_started'),
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

    /** Kode lengkap RO, mis. "4471.QDB.001" atau "2132.BEA.001". */
    private function fullCode(array $row, array $byKey): ?string
    {
        if ($row['code'] !== null && preg_match('/^[A-Z]{3}\.\d{3}$/', $row['code']) === 1) {
            // Kode RO baru (BEA.001) diawali nomor kegiatan agar unik antarkegiatan.
            for ($p = $row['parent_key']; $p !== null; $p = $byKey[$p]['parent_key']) {
                if ($byKey[$p]['level'] === 'kegiatan' && preg_match('/(\d{4})$/', (string) $byKey[$p]['code'], $m) === 1) {
                    return $m[1].'.'.$row['code'];
                }
            }

            return $row['code'];
        }
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

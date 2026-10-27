<?php

namespace App\Services\Budget;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Membaca lembar rencana anggaran (XLS/XLSX/CSV) menjadi pohon baris anggaran.
 *
 * Dua format didukung:
 *  1. POK RKA-K/L (ekspor SAKTI): tingkat dikenali dari pola kode
 *     Program 025.04.DL · Kegiatan 4471 · KRO QDB / 4471.QDB · RO 001 / 4471.QDB.001
 *     Komponen 051 · Subkomponen A · Akun 521211 · Detail (tanpa kode, diawali ">" atau "-").
 *  2. Templat RAB/RKAP Tadbira: kolom "Level" berisi nama tingkat (kelompok, pos, detail, ...).
 *
 * Hasil: daftar node berurutan, masing-masing dengan 'key' dan 'parent_key'.
 */
class BudgetSheetParser
{
    public const POK_RANK = [
        'program' => 1, 'kegiatan' => 2, 'kro' => 3, 'ro' => 4, 'komponen' => 5,
        'subkomponen' => 6, 'akun' => 7, 'detail_group' => 8, 'detail' => 9,
    ];

    public const RAB_RANK = ['kelompok' => 1, 'pos' => 2, 'detail' => 3];

    /**
     * @return array{format: 'pok'|'rab', rows: list<array<string, mixed>>}
     */
    public function parseFile(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();

        return $this->parseRows($sheet->toArray(null, true, false, false));
    }

    /**
     * @param  list<list<mixed>>  $cells
     * @return array{format: 'pok'|'rab', rows: list<array<string, mixed>>}
     */
    public function parseRows(array $cells): array
    {
        [$headerIndex, $cols] = $this->detectHeader($cells);
        $format = isset($cols['level']) ? 'rab' : 'pok';

        $rows = [];
        $stack = []; // rank => key
        $lastStructural = null;
        $key = 0;

        foreach (array_slice($cells, $headerIndex + 1) as $line) {
            $code = $this->text($line[$cols['code']] ?? null);
            $desc = $this->text($line[$cols['description']] ?? null);
            if ($code === '' && $desc === '') {
                continue;
            }
            // Baris total/jumlah dihitung ulang dari rinciannya.
            if ($code === '' && preg_match('/^(JUMLAH|TOTAL|SUB\s*TOTAL)\b/i', $desc) === 1) {
                continue;
            }
            // Baris penomoran kolom (1, 2, 3, ...) di bawah judul tabel.
            if (ctype_digit($code) && ctype_digit($desc) && (int) $desc === (int) $code + 1) {
                continue;
            }

            [$volume, $unit] = $this->volumeAndUnit($line[$cols['volume'] ?? -1] ?? null, $line[$cols['unit'] ?? -1] ?? null);
            $row = [
                'code' => $code !== '' ? $code : null,
                'description' => ltrim($desc, "->• \t"),
                'volume' => $volume,
                'unit' => $unit,
                'unit_price' => $this->number($line[$cols['unit_price'] ?? -1] ?? null),
                'amount' => $this->number($line[$cols['amount'] ?? -1] ?? null),
                'funding_source' => $this->text($line[$cols['funding_source'] ?? -1] ?? null) ?: null,
            ];

            if ($format === 'rab') {
                $level = strtolower($this->text($line[$cols['level']] ?? null)) ?: 'detail';
                $rank = self::RAB_RANK[$level] ?? (config("tadbira.budget_levels.{$level}") ? (self::POK_RANK[$level] ?? 3) : 3);
            } else {
                $level = $this->classifyPok($code, $desc, $lastStructural, $volume !== null && $row['unit'] !== null);
                if ($level === null) {
                    continue; // judul, total, atau baris yang tidak dikenali
                }
                $rank = self::POK_RANK[$level];
                if ($level !== 'detail' && $level !== 'detail_group') {
                    $lastStructural = $level;
                }
            }

            foreach (array_keys($stack) as $r) {
                if ($r >= $rank) {
                    unset($stack[$r]);
                }
            }
            $parentKey = $stack === [] ? null : $stack[max(array_keys($stack))];

            $key++;
            $rows[] = $row + [
                'key' => $key,
                'parent_key' => $parentKey,
                'level' => $level === 'detail_group' ? 'detail' : $level,
                'is_group' => $level === 'detail_group',
            ];
            $stack[$rank] = $key;
        }

        if ($rows === []) {
            throw new RuntimeException('Tidak ada baris anggaran yang dapat dibaca. Periksa apakah kolom KODE dan URAIAN tersedia.');
        }

        return ['format' => $format, 'rows' => $this->fillAmounts($rows)];
    }

    /**
     * Cari baris judul tabel dan posisi kolom berdasarkan kata kuncinya.
     *
     * @return array{0: int, 1: array<string, int>}
     */
    private function detectHeader(array $cells): array
    {
        foreach (array_slice($cells, 0, 40, true) as $i => $line) {
            $cols = [];
            foreach ($line as $c => $value) {
                $v = strtoupper($this->text($value));
                if ($v === '') {
                    continue;
                }
                match (true) {
                    ! isset($cols['code']) && preg_match('/^KODE/', $v) === 1 => $cols['code'] = $c,
                    ! isset($cols['description']) && preg_match('/URAIAN|PROGRAM\s*\/|NAMA/', $v) === 1 => $cols['description'] = $c,
                    ! isset($cols['level']) && preg_match('/^(LEVEL|TINGKAT)/', $v) === 1 => $cols['level'] = $c,
                    ! isset($cols['unit_price']) && preg_match('/HARGA/', $v) === 1 => $cols['unit_price'] = $c,
                    ! isset($cols['volume']) && preg_match('/^VOL/', $v) === 1 => $cols['volume'] = $c,
                    ! isset($cols['unit']) && preg_match('/^SAT/', $v) === 1 => $cols['unit'] = $c,
                    ! isset($cols['amount']) && preg_match('/JUMLAH|BIAYA|PAGU|TOTAL/', $v) === 1 => $cols['amount'] = $c,
                    ! isset($cols['funding_source']) && preg_match('/^(SD|SUMBER|CP)/', $v) === 1 => $cols['funding_source'] = $c,
                    default => null,
                };
            }
            if (isset($cols['code'], $cols['description'])) {
                return [$i, $cols];
            }
        }

        throw new RuntimeException('Judul kolom tidak ditemukan. Lembar harus memiliki kolom KODE dan URAIAN.');
    }

    /**
     * Tentukan tingkat baris POK dari pola kodenya.
     *
     * Catatan: tata letak ini mengikuti ekspor POK SAKTI yang umum; contoh file asli
     * pengguna diperlukan untuk memastikan semua variasi kode tertangani.
     */
    public function classifyPok(string $code, string $desc, ?string $lastStructural, bool $hasTarget = false): ?string
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            $trimmed = ltrim($desc);
            if (str_starts_with($trimmed, '>')) {
                return 'detail_group';
            }
            if (str_starts_with($trimmed, '-') || str_starts_with($trimmed, '•') || $lastStructural === 'akun') {
                return $lastStructural === null ? null : 'detail';
            }

            return null;
        }

        return match (true) {
            preg_match('/^\d{3}\.\d{2}\.[A-Z]{2}$/', $code) === 1 => 'program',
            preg_match('/^\d{4}$/', $code) === 1 => 'kegiatan',
            preg_match('/^(\d{4}\.)?[A-Z]{3}$/', $code) === 1 => 'kro',
            preg_match('/^\d{4}\.[A-Z]{3}\.\d{3}$/', $code) === 1 => 'ro',
            preg_match('/^\d{6}$/', $code) === 1 => 'akun',
            // Kode tiga angka: RO bila tepat di bawah KRO atau memuat target volume + satuan
            // (mis. "2 Prodi"); selain itu Komponen.
            preg_match('/^\d{3}$/', $code) === 1 => ($lastStructural === 'kro' || $hasTarget) ? 'ro' : 'komponen',
            preg_match('/^(\d{3}\.)?[A-Z0-9]?[A-Z]$/', $code) === 1 => 'subkomponen',
            default => null,
        };
    }

    /** Lengkapi jumlah yang kosong dengan total anak-anaknya. */
    private function fillAmounts(array $rows): array
    {
        $children = [];
        foreach ($rows as $i => $r) {
            $children[$r['parent_key'] ?? 0][] = $i;
        }
        $byKey = array_column(array_map(null, array_keys($rows), array_column($rows, 'key')), 0, 1);

        $total = function (int $i) use (&$total, &$rows, $children, $byKey): float {
            $kids = $children[$rows[$i]['key']] ?? [];
            if ($rows[$i]['amount'] === null) {
                $rows[$i]['amount'] = $kids === []
                    ? (float) (($rows[$i]['volume'] ?? 0) * ($rows[$i]['unit_price'] ?? 0))
                    : array_sum(array_map($total, $kids));
            } else {
                array_map($total, $kids);
            }

            return (float) $rows[$i]['amount'];
        };

        foreach ($children[0] ?? [] as $i) {
            $total($i);
        }

        return $rows;
    }

    private function volumeAndUnit(mixed $volume, mixed $unit): array
    {
        $unit = $this->text($unit);
        if (is_numeric($volume)) {
            return [(float) $volume, $unit ?: null];
        }
        $v = $this->text($volume);
        // Sebagian ekspor menggabungkan volume dan satuan: "12 OK", "3 Kegiatan".
        if (preg_match('/^([\d.,]+)\s*(.*)$/u', $v, $m) === 1) {
            return [$this->number($m[1]), ($unit ?: trim($m[2])) ?: null];
        }

        return [null, $unit ?: null];
    }

    public function number(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $s = preg_replace('/[^\d.,\-]/', '', (string) $value);
        if ($s === '' || $s === '-') {
            return null;
        }
        // Format Indonesia: titik pemisah ribuan, koma desimal.
        if (str_contains($s, ',') || substr_count($s, '.') > 1 || preg_match('/\.\d{3}$/', $s) === 1) {
            $s = str_replace(['.', ','], ['', '.'], $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }

    private function text(mixed $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }
}

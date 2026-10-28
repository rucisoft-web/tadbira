<?php

namespace App\Services\Budget;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Membaca lembar rencana anggaran (XLS/XLSX/CSV) menjadi pohon baris anggaran.
 *
 * Format yang didukung:
 *  1. POK RKA-K/L (ekspor SAKTI): tingkat dikenali dari pola kode, baik kode lama
 *     (Program 025.04.DL · Kegiatan 4471 · KRO QDB / 4471.QDB · RO 001 / 4471.QDB.001)
 *     maupun kode baru (Program DK · Kegiatan DK.2132 · KRO BEA · RO BEA.001),
 *     lalu Komponen 051 · Subkomponen A · Akun 521211 · Detail (tanpa kode, diawali ">" atau "-").
 *  2. Laporan Ketersediaan Dana / realisasi (SAKTI, OM-SPAN): kode menyatu dengan
 *     uraian, kolom Pagu Revisi dan Realisasi s.d. Periode. Bisa XLS atau PDF.
 *  3. Templat RAB/RKAP Tadbira: kolom "Level" berisi nama tingkat (kelompok, pos, detail, ...).
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

    /** Pola kode anggaran K/L yang dapat muncul di depan uraian. */
    public const CODE_PATTERN = '(?:\d{3}\.\d{2}\.[A-Z]{2}|[A-Z]{2}\.\d{4}|(?:[A-Z]{2}\.)?(?:\d{4}\.)?[A-Z]{3}(?:\.\d{3})?|[A-Z]{2}|\d{6}|\d{4}|\d{3}|[A-Z])';

    private const MONTHS = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
        'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    /**
     * @return array{format: 'pok'|'rab', source: string, meta: array, rows: list<array<string, mixed>>}
     */
    public function parseFile(string $path): array
    {
        $handle = fopen($path, 'rb');
        $magic = $handle ? fread($handle, 5) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($magic === '%PDF-') {
            return $this->parseRows((new PdfReportReader)->toRows($path));
        }

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
        $meta = $this->meta(array_slice($cells, 0, $headerIndex + 1));
        // Tanpa kolom KODE, kode ditulis menyatu di depan uraian (laporan SAKTI/OM-SPAN).
        $embedded = ! isset($cols['code']);

        $rows = [];
        $stack = []; // rank => key
        $lastStructural = null;
        $key = 0;

        foreach (array_slice($cells, $headerIndex + 1) as $line) {
            if ($embedded) {
                [$code, $desc] = $this->splitCode($line, $cols);
            } else {
                $code = $this->text($line[$cols['code']] ?? null);
                $desc = $this->text($line[$cols['description']] ?? null);
            }
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
                'realized' => isset($cols['realized']) ? $this->number($line[$cols['realized']] ?? null) : null,
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

        return [
            'format' => $format,
            'source' => isset($cols['realized']) ? 'realisasi' : $format,
            'meta' => $meta,
            'rows' => $this->fillAmounts($rows),
        ];
    }

    /**
     * Pisahkan kode dari uraian, mis. "BEA.001 Tunjangan Profesi Dosen Non ASN".
     * Ekspor XLS kadang menaruh kode di sel terpisah di kiri uraian, jadi semua
     * teks di kiri kolom angka pertama digabung lebih dulu.
     *
     * @return array{0: string, 1: string}
     */
    private function splitCode(array $line, array $cols): array
    {
        $firstNumber = min(array_diff_key($cols, ['description' => 0]) ?: [PHP_INT_MAX]);
        $parts = [];
        foreach ($line as $c => $value) {
            if ($c >= $cols['description'] && $c < $firstNumber && $this->text($value) !== '') {
                $parts[] = $this->text($value);
            }
        }
        // Kode bisa berada di kolom sebelum kolom "Uraian" bila judulnya digabung.
        for ($c = $cols['description'] - 1; $c >= 0; $c--) {
            if ($this->text($line[$c] ?? null) !== '') {
                array_unshift($parts, $this->text($line[$c]));
            }
        }
        $text = implode(' ', $parts);

        if (preg_match('/^('.self::CODE_PATTERN.')\s+(.+)$/u', $text, $m) === 1) {
            return [$m[1], $m[2]];
        }

        return ['', $text];
    }

    /**
     * Keterangan di atas tabel: tahun anggaran, periode laporan, dan satuan kerja.
     */
    private function meta(array $lines): array
    {
        $meta = ['year' => null, 'period_end' => null, 'period_label' => null, 'unit' => null];
        foreach ($lines as $line) {
            $text = $this->text(implode(' ', array_map(fn ($v) => $this->text($v), $line)));
            if ($meta['year'] === null && preg_match('/\bTA\.?\s*(\d{4})\b/i', $text, $m) === 1) {
                $meta['year'] = (int) $m[1];
            }
            if ($meta['period_end'] === null && preg_match('/PERIODE\s+([A-Za-z]+)\s+(\d{4})/i', $text, $m) === 1
                && isset(self::MONTHS[strtolower($m[1])])) {
                $meta['period_end'] = date('Y-m-t', mktime(0, 0, 0, self::MONTHS[strtolower($m[1])], 1, (int) $m[2]));
                $meta['period_label'] = ucfirst(strtolower($m[1])).' '.$m[2];
            }
            if ($meta['unit'] === null && preg_match('/SATUAN\s+KERJA\s*:?\s*(\d{6})?\s*(.+?)(\s+HAL\b.*)?$/i', $text, $m) === 1) {
                $meta['unit'] = trim($m[2]);
            }
        }

        return $meta;
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
                    ! isset($cols['realized']) && preg_match('/^S\.?\s*D\.?\s*PERIODE|^S\/D\b|^REALISASI\s+S/', $v) === 1 => $cols['realized'] = $c,
                    ! isset($cols['amount']) && preg_match('/JUMLAH|BIAYA|PAGU|TOTAL/', $v) === 1 => $cols['amount'] = $c,
                    ! isset($cols['funding_source']) && preg_match('/^(SD|SUMBER|CP)/', $v) === 1 => $cols['funding_source'] = $c,
                    default => null,
                };
            }
            if (isset($cols['code'], $cols['description'])) {
                return [$i, $cols];
            }
            if (isset($cols['description'], $cols['amount'])) {
                // Judul dua tingkat: "Realisasi" di atas "Periode Lalu | Periode Ini | s.d. Periode".
                $next = $cells[$i + 1] ?? [];
                foreach ($next as $c => $value) {
                    $v = strtoupper($this->text($value));
                    if (! isset($cols['realized']) && preg_match('/^S\.?\s*D\.?\s*PERIODE|^S\/D\b/', $v) === 1) {
                        $cols['realized'] = $c;
                    }
                }
                $isSubHeader = $next !== [] && array_filter($next, fn ($v) => is_numeric($v) || preg_match('/\d{3}[.,]\d{3}/', (string) $v) === 1) === [];

                return [$isSubHeader ? $i + 1 : $i, $cols];
            }
        }

        throw new RuntimeException('Judul kolom tidak ditemukan. Lembar harus memiliki kolom KODE dan URAIAN, atau URAIAN dan PAGU.');
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

        $underKomponen = in_array($lastStructural, ['komponen', 'subkomponen', 'akun'], true);

        return match (true) {
            preg_match('/^\d{3}\.\d{2}\.[A-Z]{2}$/', $code) === 1 => 'program',
            // Kode program baru dua huruf (mis. DK); di bawah komponen, dua huruf adalah subkomponen.
            preg_match('/^[A-Z]{2}$/', $code) === 1 => $underKomponen ? 'subkomponen' : 'program',
            preg_match('/^([A-Z]{2}\.)?\d{4}$/', $code) === 1 => 'kegiatan',
            preg_match('/^([A-Z]{2}\.)?(\d{4}\.)?[A-Z]{3}$/', $code) === 1 => 'kro',
            preg_match('/^([A-Z]{2}\.)?(\d{4}\.)?[A-Z]{3}\.\d{3}$/', $code) === 1 => 'ro',
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
        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $s) === 1) {
            // Format Inggris (laporan SAKTI/OM-SPAN): koma pemisah ribuan.
            $s = str_replace(',', '', $s);
        } elseif (str_contains($s, ',') || substr_count($s, '.') > 1 || preg_match('/\.\d{3}$/', $s) === 1) {
            // Format Indonesia: titik pemisah ribuan, koma desimal.
            $s = str_replace(['.', ','], ['', '.'], $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }

    private function text(mixed $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }
}

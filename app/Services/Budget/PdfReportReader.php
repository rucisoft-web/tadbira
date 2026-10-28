<?php

namespace App\Services\Budget;

use RuntimeException;
use Smalot\PdfParser\Parser;

/**
 * Mengubah laporan anggaran berbentuk PDF (mis. "Laporan Ketersediaan Dana Detail"
 * dari SAKTI/OM-SPAN) menjadi baris-baris sel seperti lembar kerja, sehingga
 * dapat dibaca oleh BudgetSheetParser.
 *
 * Setiap teks di PDF memiliki posisi. Teks dikelompokkan per baris berdasarkan
 * posisi vertikalnya, lalu angka dipetakan ke kolom judul tabel berdasarkan
 * urutan horizontalnya. Uraian yang terbungkus ke baris berikutnya digabungkan
 * kembali ke baris berkode terdekat.
 */
class PdfReportReader
{
    /** Judul kolom yang dikenali, dari kiri ke kanan. */
    private const COLUMNS = [
        'pagu' => '/^PAGU/',
        'lock' => '/^LOCK/',
        'lalu' => '/^PERIODE\s+LALU/',
        'ini' => '/^PERIODE\s+INI/',
        'sd' => '/^S\.?\s*D\.?\s*PERIODE/',
        'persen' => '/^%$/',
        'sisa' => '/^SISA/',
    ];

    private const HEADER = [
        'pagu' => 'Pagu Revisi', 'lock' => 'Lock Pagu', 'lalu' => 'Periode Lalu', 'ini' => 'Periode Ini',
        'sd' => 's.d. Periode', 'persen' => '%', 'sisa' => 'Sisa Anggaran',
    ];

    /**
     * @return list<list<string|null>>
     */
    public function toRows(string $path): array
    {
        $pdf = (new Parser)->parseFile($path);
        $out = [];
        $headerWritten = false;

        foreach ($pdf->getPages() as $page) {
            $tokens = [];
            foreach ($page->getDataTm() as [$matrix, $text]) {
                $text = trim((string) $text);
                if ($text !== '') {
                    $tokens[] = ['a' => (float) $matrix[4], 'b' => (float) $matrix[5], 'text' => $text];
                }
            }
            $layout = $this->layout($tokens);
            if ($layout === null) {
                // Halaman tanpa tabel: simpan teksnya agar keterangan (TA, periode) tetap terbaca.
                foreach ($tokens as $t) {
                    $out[] = [$t['text']];
                }

                continue;
            }

            [$row, $col, $headerEnd, $columns, $uraianEnd] = $layout;

            // Kepala halaman (judul, satker, periode) sebagai baris teks biasa.
            if (! $headerWritten) {
                foreach ($this->groupRows(array_filter($tokens, fn ($t) => $row($t) < $headerEnd - 25), $row) as $line) {
                    usort($line, fn ($x, $y) => $col($x) <=> $col($y));
                    $out[] = [implode(' ', array_column($line, 'text'))];
                }
                $out[] = array_merge(['Uraian'], array_values(array_intersect_key(self::HEADER, $columns)));
                $headerWritten = true;
            }

            $body = array_filter($tokens, fn ($t) => $row($t) > $headerEnd + 1 && ! str_starts_with($t['text'], '*'));
            foreach ($this->mergeWrapped($this->groupRows($body, $row), $row, $col, $uraianEnd) as $line) {
                $out[] = $this->cells($line, $row, $col, $columns, $uraianEnd);
            }
        }

        if ($out === []) {
            throw new RuntimeException('PDF tidak berisi teks yang dapat dibaca. Gunakan PDF hasil unduhan aplikasi, bukan hasil pindai.');
        }

        return $out;
    }

    /**
     * Tentukan sumbu baris/kolom dan posisi judul kolom.
     *
     * Sebagian PDF berorientasi lanskap diputar, sehingga sumbu x dan y tertukar.
     * Sumbu baris adalah sumbu tempat "Uraian" dan "Pagu Revisi" bernilai sama.
     */
    private function layout(array $tokens): ?array
    {
        $uraian = $this->find($tokens, '/^URAIAN$/');
        $pagu = $this->find($tokens, self::COLUMNS['pagu']);
        if ($uraian === null || $pagu === null) {
            return null;
        }

        $rowAxis = abs($uraian['a'] - $pagu['a']) < abs($uraian['b'] - $pagu['b']) ? 'a' : 'b';
        $colAxis = $rowAxis === 'a' ? 'b' : 'a';
        // Pada sumbu y PDF biasa nilai membesar ke atas; balik agar membesar ke bawah.
        $sign = $rowAxis === 'b' ? -1 : 1;
        $row = fn ($t) => $sign * $t[$rowAxis];
        $col = fn ($t) => $t[$colAxis];

        $columns = [];
        $headerEnd = $row($uraian);
        foreach (self::COLUMNS as $key => $pattern) {
            $t = $this->find($tokens, $pattern);
            if ($t !== null && abs($row($t) - $row($uraian)) < 30) {
                $columns[$key] = $col($t);
                $headerEnd = max($headerEnd, $row($t));
            }
        }
        // Kolom SISA ANGGARAN ditulis dua baris; ambil juga baris keduanya.
        $anggaran = $this->find($tokens, '/^ANGGARAN$/');
        if ($anggaran !== null && abs($row($anggaran) - $row($uraian)) < 30) {
            $headerEnd = max($headerEnd, $row($anggaran));
        }
        asort($columns);

        return [$row, $col, $headerEnd, $columns, $columns['pagu'] - 2];
    }

    private function find(array $tokens, string $pattern): ?array
    {
        foreach ($tokens as $t) {
            if (preg_match($pattern, strtoupper($t['text'])) === 1) {
                return $t;
            }
        }

        return null;
    }

    /**
     * Kelompokkan teks yang posisi vertikalnya berdekatan menjadi satu baris.
     *
     * @return list<list<array>>
     */
    private function groupRows(array $tokens, callable $row): array
    {
        usort($tokens, fn ($x, $y) => $row($x) <=> $row($y));
        $lines = [];
        $current = [];
        $last = null;
        foreach ($tokens as $t) {
            if ($last !== null && $row($t) - $last > 2.5) {
                $lines[] = $current;
                $current = [];
            }
            $current[] = $t;
            $last = $row($t);
        }
        if ($current !== []) {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * Baris yang hanya berisi sambungan uraian (tanpa kode dan angka) digabung
     * ke baris berangka terdekat.
     */
    private function mergeWrapped(array $lines, callable $row, callable $col, float $uraianEnd): array
    {
        $isData = fn ($line) => array_filter($line, fn ($t) => $col($t) >= $uraianEnd) !== [];
        $center = fn ($line) => array_sum(array_map($row, $line)) / count($line);

        $data = [];
        $orphans = [];
        foreach ($lines as $line) {
            if ($isData($line)) {
                $data[] = $line;
            } else {
                $orphans[] = $line;
            }
        }
        foreach ($orphans as $orphan) {
            $best = null;
            foreach ($data as $i => $line) {
                $d = abs($center($line) - $center($orphan));
                if ($best === null || $d < $best[1]) {
                    $best = [$i, $d];
                }
            }
            if ($best !== null && $best[1] < 12) {
                $data[$best[0]] = array_merge($data[$best[0]], $orphan);
            }
        }

        return $data;
    }

    /**
     * @return list<string|null>
     */
    private function cells(array $line, callable $row, callable $col, array $columns, float $uraianEnd): array
    {
        $text = array_values(array_filter($line, fn ($t) => $col($t) < $uraianEnd));
        $numbers = array_values(array_filter($line, fn ($t) => $col($t) >= $uraianEnd));

        // Uraian yang terbungkus diurutkan dari atas ke bawah, lalu dari kiri ke kanan.
        usort($text, fn ($x, $y) => [round($row($x)), $col($x)] <=> [round($row($y)), $col($y)]);
        $code = '';
        $desc = [];
        foreach ($text as $t) {
            if ($code === '' && $this->looksLikeCode($t['text'])) {
                $code = $t['text'];
            } else {
                $desc[] = $t['text'];
            }
        }
        $uraian = trim($code.' '.implode(' ', $desc));

        usort($numbers, fn ($x, $y) => $col($x) <=> $col($y));
        $values = array_fill_keys(array_keys($columns), null);
        $keys = array_keys($columns);
        $plain = array_values(array_filter($numbers, fn ($t) => ! str_ends_with($t['text'], '%')));
        $percent = array_values(array_filter($numbers, fn ($t) => str_ends_with($t['text'], '%')));
        $numericKeys = array_values(array_diff($keys, ['persen']));

        if (count($plain) === count($numericKeys)) {
            // Semua kolom terisi: petakan berdasarkan urutan.
            foreach ($numericKeys as $i => $k) {
                $values[$k] = $plain[$i]['text'];
            }
        } else {
            // Ada kolom kosong: pilih kolom yang judulnya paling dekat di kiri angka.
            foreach ($plain as $t) {
                $target = $numericKeys[0];
                foreach ($numericKeys as $k) {
                    if ($columns[$k] <= $col($t) + 8) {
                        $target = $k;
                    }
                }
                $values[$target] = $t['text'];
            }
        }
        if (array_key_exists('persen', $values) && $percent !== []) {
            $values['persen'] = $percent[0]['text'];
        }

        return array_merge([$uraian], array_values($values));
    }

    private function looksLikeCode(string $text): bool
    {
        return preg_match('/^'.BudgetSheetParser::CODE_PATTERN.'$/', $text) === 1;
    }
}

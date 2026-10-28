<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Buku kerja Excel sederhana: satu lembar per tabel, baris judul tebal berlatar hijau muda,
 * kolom uang berformat ribuan dan kolom persen/indeks berformat desimal.
 */
class Workbook
{
    private Spreadsheet $book;

    public function __construct()
    {
        $this->book = new Spreadsheet;
        $this->book->removeSheetByIndex(0);
    }

    /**
     * @param  array<string, string>  $columns  judul kolom => format: text, money, number, decimal, percent, date
     */
    public function sheet(string $title, array $columns, iterable $rows, ?string $caption = null): self
    {
        $sheet = $this->book->createSheet();
        $sheet->setTitle(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', ' ', $title), 0, 31));
        $r = 1;
        if ($caption) {
            $sheet->setCellValue('A1', $caption);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
            $r = 3;
        }

        $headerRow = $r;
        $formats = array_values($columns);
        $sheet->fromArray([array_keys($columns)], null, "A{$r}");
        foreach ($rows as $row) {
            $r++;
            $sheet->fromArray([array_values($row)], null, "A{$r}", true);
        }

        $last = Coordinate::stringFromColumnIndex(count($columns));
        $head = $sheet->getStyle("A{$headerRow}:{$last}{$headerRow}");
        $head->getFont()->setBold(true);
        $head->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
        $sheet->freezePane('A'.($headerRow + 1));
        if ($r > $headerRow) {
            $sheet->setAutoFilter("A{$headerRow}:{$last}{$r}");
        }

        foreach ($formats as $i => $format) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $code = ['money' => '#,##0', 'number' => '#,##0', 'decimal' => '0.00', 'percent' => '0.0"%"', 'date' => 'dd/mm/yyyy'][$format] ?? null;
            if ($code && $r > $headerRow) {
                $sheet->getStyle("{$col}".($headerRow + 1).":{$col}{$r}")->getNumberFormat()->setFormatCode($code);
            }
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this;
    }

    public function download(string $filename): StreamedResponse
    {
        $this->book->setActiveSheetIndex(0);

        return response()->streamDownload(function () {
            (new Xlsx($this->book))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Nilai tanggal Excel dari string Y-m-d (atau null). */
    public static function date(?string $date): mixed
    {
        return $date ? Date::stringToExcel($date) : null;
    }
}

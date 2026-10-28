<?php

namespace App\Services\Performance;

/**
 * Analisis nilai hasil (Earned Value Management, PMBOK).
 *
 * BAC  anggaran saat selesai (baseline)       PV  nilai rencana = BAC × % rencana per hari ini
 * EV   nilai hasil = BAC × % selesai           AC  biaya aktual (realisasi dibayar)
 * SV = EV − PV, CV = EV − AC, SPI = EV / PV, CPI = EV / AC
 * EAC = BAC / CPI (perkiraan biaya akhir), ETC = EAC − AC, VAC = BAC − EAC
 * TCPI = (BAC − EV) / (BAC − AC): efisiensi yang dibutuhkan agar tetap dalam anggaran.
 */
final class EarnedValue
{
    public static function compute(float $bac, float $plannedPercent, float $progressPercent, float $ac): array
    {
        $pv = $bac * min(100, max(0, $plannedPercent)) / 100;
        $ev = $bac * min(100, max(0, $progressPercent)) / 100;
        $spi = $pv > 0 ? $ev / $pv : null;
        $cpi = $ac > 0 ? $ev / $ac : null;
        $eac = $cpi && $cpi > 0 ? $bac / $cpi : $bac;
        $remaining = $bac - $ac;

        return [
            'bac' => round($bac, 2),
            'pv' => round($pv, 2),
            'ev' => round($ev, 2),
            'ac' => round($ac, 2),
            'sv' => round($ev - $pv, 2),
            'cv' => round($ev - $ac, 2),
            'spi' => $spi !== null ? round($spi, 2) : null,
            'cpi' => $cpi !== null ? round($cpi, 2) : null,
            'eac' => round($eac, 2),
            'etc' => round(max(0, $eac - $ac), 2),
            'vac' => round($bac - $eac, 2),
            'tcpi' => $remaining > 0 ? round(($bac - $ev) / $remaining, 2) : null,
        ];
    }
}

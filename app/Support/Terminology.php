<?php

namespace App\Support;

/**
 * Istilah yang tampil di antarmuka, disesuaikan dengan sektor organisasi
 * (mis. "Pagu DIPA" untuk K/L, "Anggaran RKAP" untuk BUMN).
 */
class Terminology
{
    public function __construct(private ?string $sector = null)
    {
    }

    public function get(string $key): string
    {
        $sectorTerms = config("tadbira.sector_terms.{$this->sector}", []);

        return $sectorTerms[$key] ?? config("tadbira.terms.{$key}", $key);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return array_merge(
            config('tadbira.terms', []),
            config("tadbira.sector_terms.{$this->sector}", []),
        );
    }
}

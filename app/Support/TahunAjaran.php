<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Semester 1: Juli-Desember, Semester 2: Januari-Juni.
 * Tahun ajaran ditulis dengan tahun awalnya (2025 = 2025/2026).
 */
class TahunAjaran
{
    public static function semester(?CarbonInterface $date = null): int
    {
        $date = $date ?? now();

        return $date->month >= 7 ? 1 : 2;
    }

    public static function tahun(?CarbonInterface $date = null): int
    {
        $date = $date ?? now();

        return $date->month >= 7 ? $date->year : $date->year - 1;
    }

    public static function options(int $jumlah = 6): array
    {
        $tahun = static::tahun();

        return range($tahun, $tahun - $jumlah + 1);
    }
}

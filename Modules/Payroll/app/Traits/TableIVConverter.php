<?php

namespace Modules\Payroll\Traits;

/**
 * TableIVConverter
 *
 * Provides Table IV conversion logic (COA/DBM standard).
 * Based on an 8-hour workday and 22-working-day fixed denominator.
 *
 * Source: Payroll_and_Work_Conversion_Reference.pdf
 */
trait TableIVConverter
{
    /**
     * Official Table IV minute-to-day equivalents (1–60 minutes).
     * Key = minutes, Value = decimal day equivalent.
     */
    private static array $minuteTable = [
        1  => 0.002,  2  => 0.004,  3  => 0.006,  4  => 0.008,  5  => 0.010,
        6  => 0.012,  7  => 0.015,  8  => 0.017,  9  => 0.019,  10 => 0.021,
        11 => 0.023,  12 => 0.025,  13 => 0.027,  14 => 0.029,  15 => 0.031,
        16 => 0.033,  17 => 0.035,  18 => 0.037,  19 => 0.040,  20 => 0.042,
        21 => 0.044,  22 => 0.046,  23 => 0.048,  24 => 0.050,  25 => 0.052,
        26 => 0.054,  27 => 0.056,  28 => 0.058,  29 => 0.060,  30 => 0.062,
        31 => 0.065,  32 => 0.067,  33 => 0.069,  34 => 0.071,  35 => 0.073,
        36 => 0.075,  37 => 0.077,  38 => 0.079,  39 => 0.081,  40 => 0.083,
        41 => 0.085,  42 => 0.087,  43 => 0.090,  44 => 0.092,  45 => 0.094,
        46 => 0.096,  47 => 0.098,  48 => 0.100,  49 => 0.102,  50 => 0.104,
        51 => 0.106,  52 => 0.108,  53 => 0.110,  54 => 0.112,  55 => 0.115,
        56 => 0.117,  57 => 0.119,  58 => 0.121,  59 => 0.123,  60 => 0.125,
    ];

    /**
     * Official Table IV hour-to-day equivalents (1–8 hours).
     */
    private static array $hourTable = [
        1 => 0.125,
        2 => 0.250,
        3 => 0.375,
        4 => 0.500,
        5 => 0.625,
        6 => 0.750,
        7 => 0.875,
        8 => 1.000,
    ];

    /**
     * Convert total minutes to decimal day equivalent using Table IV.
     *
     * For minutes > 60, convert whole hours first, then remaining minutes.
     * e.g. 95 minutes = 1 hour (0.125) + 35 minutes (0.073) = 0.198 days
     *
     * @param  int   $totalMinutes
     * @return float decimal day equivalent
     */
    public function minutesToDays(int $totalMinutes): float
    {
        if ($totalMinutes <= 0) return 0.0;

        $hours   = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        $dayEquiv = 0.0;

        // Add whole hours (max 8)
        if ($hours > 0) {
            $hours = min($hours, 8);
            $dayEquiv += self::$hourTable[$hours] ?? ($hours * 0.125);
        }

        // Add remaining minutes
        if ($minutes > 0) {
            $dayEquiv += self::$minuteTable[$minutes] ?? round($minutes / 480, 3);
        }

        return round($dayEquiv, 3);
    }

    /**
     * Convert hours to decimal day equivalent using Table IV.
     *
     * @param  int   $hours  1–8
     * @return float
     */
    public function hoursToDays(int $hours): float
    {
        $hours = max(0, min(8, $hours));
        return self::$hourTable[$hours] ?? round($hours * 0.125, 3);
    }

    /**
     * Look up the exact Table IV equivalent for N minutes (1–60 only).
     * Returns null for out-of-range values; use minutesToDays() for > 60.
     */
    public function tableIVMinute(int $minutes): ?float
    {
        return self::$minuteTable[$minutes] ?? null;
    }
}
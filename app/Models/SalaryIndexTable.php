<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryIndexTable extends Model
{
    protected $fillable = [
        'salary_grade',
        'step',
        'year',
        'amount',
    ];

    protected $casts = [
        'salary_grade' => 'integer',
        'step'         => 'integer',
        'year'         => 'integer',
        'amount'       => 'decimal:2',
    ];

    // No soft deletes — reference data only.
    public $timestamps = true;

    // ── Scopes ────────────────────────────────────────────────────

    /**
     * Quickly fetch the amount for a given SG / Step / Year.
     * Returns null if not found.
     *
     * Usage: SalaryIndexTable::amountFor(19, 4, 2022)
     */
    public static function amountFor(int $sg, int $step, int $year): ?float
    {
        return static::where('salary_grade', $sg)
            ->where('step', $step)
            ->where('year', $year)
            ->value('amount');
    }

    /**
     * Return all steps (1–8) for a given SG + Year, keyed by step number.
     * Useful for populating step dropdowns with amounts.
     *
     * Usage: SalaryIndexTable::stepsFor(19, 2022)
     *   → [1 => 49835.00, 2 => 50574.00, ...]
     */
    public static function stepsFor(int $sg, int $year): array
    {
        return static::where('salary_grade', $sg)
            ->where('year', $year)
            ->orderBy('step')
            ->pluck('amount', 'step')
            ->toArray();
    }
}
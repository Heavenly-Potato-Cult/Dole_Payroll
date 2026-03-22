<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePromotionHistory extends Model
{
    protected $fillable = [
        'employee_id',
        'effective_date',
        'old_sg',
        'old_step',
        'old_salary',
        'new_sg',
        'new_step',
        'new_salary',
        'type',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'old_sg'         => 'integer',
        'old_step'       => 'integer',
        'old_salary'     => 'decimal:2',
        'new_sg'         => 'integer',
        'new_step'       => 'integer',
        'new_salary'     => 'decimal:2',
    ];

    // ── Type constants ────────────────────────────────────────────
    const TYPE_PROMOTION       = 'promotion';
    const TYPE_STEP_INCREMENT  = 'step_increment';
    const TYPE_ADJUSTMENT      = 'adjustment';

    const TYPES = [
        self::TYPE_PROMOTION      => 'Promotion',
        self::TYPE_STEP_INCREMENT => 'Step Increment',
        self::TYPE_ADJUSTMENT     => 'Salary Adjustment',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Computed ──────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDifferentialAttribute(): float
    {
        return round($this->new_salary - $this->old_salary, 2);
    }
}
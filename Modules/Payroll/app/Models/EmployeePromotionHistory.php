<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePromotionHistory extends Model
{
    protected $table = 'employee_promotion_history'; // ← add this

    protected $fillable = [
        'employee_id',
        'effectivity_date',       // was: effective_date
        'type',
        'old_salary_grade',       // was: old_sg
        'old_step',
        'old_basic_salary',       // was: old_salary
        'new_salary_grade',       // was: new_sg
        'new_step',
        'new_basic_salary',       // was: new_salary
        'csb_no',
        'remarks',
        'recorded_by',            // was: created_by
    ];

    protected $casts = [
        'effectivity_date' => 'date',
        'old_salary_grade' => 'integer',
        'old_step'         => 'integer',
        'old_basic_salary' => 'decimal:2',
        'new_salary_grade' => 'integer',
        'new_step'         => 'integer',
        'new_basic_salary' => 'decimal:2',
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
        return $this->belongsTo(\App\SharedKernel\Models\Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
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

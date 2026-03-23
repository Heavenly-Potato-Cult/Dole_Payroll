<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plantilla_item_no',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'position_title',
        'division_id',
        'salary_grade',
        'step',
        'sit_year',
        'basic_salary',
        'pera',
        'hire_date',
        'status',
        'employment_type',   // regular | vacant
        'tin',
        'gsis_bp_no',
        'pagibig_no',
        'philhealth_no',
        'sss_no',
    ];

    protected $casts = [
        'salary_grade' => 'integer',
        'step'         => 'integer',
        'sit_year'     => 'integer',
        'basic_salary' => 'decimal:2',
        'pera'         => 'decimal:2',
        'hire_date'    => 'date',
    ];

    // ── Status constants ─────────────────────────────────────────
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_VACANT   = 'vacant';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_VACANT,
    ];

    // ── Relationships ────────────────────────────────────────────

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function promotionHistory(): HasMany
    {
        return $this->hasMany(EmployeePromotionHistory::class)->orderByDesc('effective_date');
    }

    /**
     * Primary relationship name used in views/forms.
     */
    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeductionEnrollment::class);
    }

    /**
     * Alias — PayrollComputationService and AttendanceService use this name.
     */
    public function deductionEnrollments(): HasMany
    {
        return $this->hasMany(EmployeeDeductionEnrollment::class);
    }

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    // ── Computed helpers ─────────────────────────────────────────

    /**
     * Full name: LAST, First MI.
     */
    public function getFullNameAttribute(): string
    {
        $mi     = $this->middle_name ? ' ' . mb_strtoupper(mb_substr($this->middle_name, 0, 1)) . '.' : '';
        $suffix = $this->suffix ? ', ' . $this->suffix : '';
        return $this->last_name . ', ' . $this->first_name . $mi . $suffix;
    }

    /**
     * Display name for payslips: First MI. Last
     */
    public function getDisplayNameAttribute(): string
    {
        $mi = $this->middle_name ? ' ' . mb_strtoupper(mb_substr($this->middle_name, 0, 1)) . '.' : '';
        return $this->first_name . $mi . ' ' . $this->last_name;
    }

    /**
     * Daily rate = basic_salary / 22
     */
    public function getDailyRateAttribute(): float
    {
        return round($this->basic_salary / 22, 4);
    }

    /**
     * Hourly rate = basic_salary / 22 / 8
     */
    public function getHourlyRateAttribute(): float
    {
        return round($this->basic_salary / 22 / 8, 4);
    }

    /**
     * Minute rate = basic_salary / 22 / 8 / 60
     */
    public function getMinuteRateAttribute(): float
    {
        return round($this->basic_salary / 22 / 8 / 60, 6);
    }

    /**
     * Semi-monthly gross = (basic_salary + pera) / 2
     */
    public function getSemiMonthlyGrossAttribute(): float
    {
        return round(($this->basic_salary + $this->pera) / 2, 2);
    }

    // ── Attribute aliases for PayrollComputationService ──────────
    // The service was written expecting these names. These aliases
    // map them to the actual column names in the database.

    /**
     * Alias: basic_monthly_salary → basic_salary column
     * Used by: PayrollComputationService, AttendanceService
     */
    public function getBasicMonthlySalaryAttribute(): float
    {
        return (float) $this->basic_salary;
    }

    /**
     * Alias: pera_amount → pera column
     * Used by: PayrollComputationService
     */
    public function getPeraAmountAttribute(): float
    {
        return (float) $this->pera;
    }

    /**
     * RATA allowance — not all employees have this.
     * Returns 0.00 if not set on the model.
     * Used by: PayrollComputationService
     */
    public function getRataAttribute(): float
    {
        return (float) ($this->attributes['rata'] ?? 0);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByDivision($query, int $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }
}
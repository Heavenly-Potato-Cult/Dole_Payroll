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

    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeductionEnrollment::class);
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
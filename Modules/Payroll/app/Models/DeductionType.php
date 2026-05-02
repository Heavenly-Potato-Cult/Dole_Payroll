<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class DeductionType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'display_order',
        'category',
        'is_computed',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_computed' => 'boolean',
        'is_active'   => 'boolean',
        'display_order' => 'integer',
    ];

    // ── Categories (keep your constants) ────────────────────────────────
    const CAT_PAGIBIG   = 'pagibig';
    const CAT_PHILHEALTH = 'philhealth';
    const CAT_GSIS      = 'gsis';
    const CAT_OTHER_GOV = 'other_gov';
    const CAT_LOAN      = 'loan';
    const CAT_CARESS    = 'caress';
    const CAT_MISC      = 'misc';

    // ── Relationships ─────────────────────────────────────────────
    public function enrollments(): HasMany
    {
        return $this->hasMany(EmployeeDeductionEnrollment::class);
    }

    public function payrollDeductions(): HasMany
    {
        return $this->hasMany(PayrollDeduction::class);
    }

    // ── Scopes (keep both - yours and friend's) ────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function scopeComputed($query)
    {
        return $query->where('is_computed', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_computed', false);
    }
    
    // Add your friend's typed scope methods (they're the same, just typed)
    // This helps with IDE autocomplete
    public function scopeActiveTyped(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderedTyped(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}

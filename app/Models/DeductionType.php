<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // ── Categories ────────────────────────────────────────────────
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

    // ── Scopes ────────────────────────────────────────────────────
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
}
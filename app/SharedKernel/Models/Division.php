<?php

namespace App\SharedKernel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────

    /**
     * Employees assigned to this division.
     * Assumes Employee model has division_id FK.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(\App\SharedKernel\Models\Employee::class);
    }
}
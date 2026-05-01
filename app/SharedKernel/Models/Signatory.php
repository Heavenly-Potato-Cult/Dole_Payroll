<?php

namespace App\SharedKernel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Signatory
 * ─────────────────────────────────────────────────────────────────────────────
 * Represents a signing officer for a specific document role (e.g. hrmo_designate,
 * accountant, ard, cashier). Each role type has at most one active signatory.
 *
 * Name resolution (used on payslips):
 *   $signatory->displayName()       → full_name override OR user->name
 *   $signatory->displayTitle()      → position_title override OR ''
 *
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $role_type
 * @property string|null $full_name       Optional display-name override
 * @property string|null $position_title
 * @property bool        $is_active
 */
class Signatory extends Model
{
    protected $fillable = [
        'user_id',
        'role_type',
        'full_name',
        'position_title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ── Display helpers (used in blade & PDF views) ──────────────────────────

    /**
     * The name that should appear on payslips.
     * Prefers the manual override; falls back to the linked user's name.
     */
    public function displayName(): string
    {
        return $this->full_name
            ?? $this->user?->name
            ?? 'HRMO DESIGNATE';
    }

    /**
     * The title that should appear on payslips.
     */
    public function displayTitle(): string
    {
        return $this->position_title ?? '';
    }
}

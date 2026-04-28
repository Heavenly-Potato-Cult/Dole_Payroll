<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserRoleAssignment
 * ─────────────────────────────────────────────────────────────────────────────
 * Tracks which users hold which roles, and whether each assignment is currently
 * active. Works alongside Spatie's model_has_roles — Spatie controls access
 * gates; this model controls "who is the acting officer right now."
 *
 * @property int     $id
 * @property int     $user_id
 * @property string  $role_name
 * @property bool    $is_active
 */
class UserRoleAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'role_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Only active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by a specific role name.
     */
    public function scopeForRole($query, string $roleName)
    {
        return $query->where('role_name', $roleName);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\SharedKernel\Models\Employee;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * Get the employee associated with this user.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * All role assignment tracking rows for this user.
     * One row per role the user has ever been assigned.
     */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    /**
     * Only the currently active role assignments.
     */
    public function activeRoleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class)->where('is_active', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Check whether this user is the active officer for a given role.
     * Used by the sidebar and signatory resolution logic.
     */
    public function isActiveFor(string $roleName): bool
    {
        return $this->roleAssignments()
                    ->where('role_name', $roleName)
                    ->where('is_active', true)
                    ->exists();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * user_role_assignments
 * ─────────────────────────────────────────────────────────────────────────────
 * Sits alongside Spatie's model_has_roles table.
 * Spatie still drives @role() gates and middleware — this table adds the
 * "active / inactive" layer so the system knows who is the *current acting*
 * officer for a given role at any point in time.
 *
 * Why a separate table instead of modifying Spatie's pivot?
 *   • Spatie's pivot has a composite PK that is hard to extend cleanly.
 *   • Keeping our concerns separate means Spatie upgrades are non-breaking.
 *   • We can query "who is the active ARD right now?" without touching
 *     Spatie internals.
 *
 * Rules enforced at the application layer (SignatoryController / UserController):
 *   • A user may appear in this table once per role_name.
 *   • Multiple users may hold the same role_name, each with their own
 *     is_active flag — but only ONE should be active per role_name at a time
 *     (enforced in UserController::activateRole()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Matches the Spatie role name exactly, e.g. 'ard', 'hrmo', 'cashier'.
            // Kept as a plain string (no FK to roles table) so this table stays
            // independent of Spatie's schema.
            $table->string('role_name', 60);

            // true  → this user is the CURRENT acting officer for this role.
            // false → they previously held it but have been replaced / are on leave.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // A user holds each role only once in this tracking table.
            $table->unique(['user_id', 'role_name']);

            // Fast lookup: "who is the active ARD?"
            $table->index(['role_name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_assignments');
    }
};

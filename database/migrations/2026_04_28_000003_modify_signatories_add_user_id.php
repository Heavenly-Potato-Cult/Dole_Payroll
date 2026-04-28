<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modify signatories table
 * ─────────────────────────────────────────────────────────────────────────────
 * Previously, signatories stored a manually typed full_name and position_title.
 * Now a signatory points to a real system user (via user_id), and the name/title
 * fields become optional overrides — useful if the signing name on a document
 * needs to differ from the user's account name (e.g. formal vs. casual name).
 *
 * Resolution order when printing on payslips:
 *   1. signatory.full_name      (if manually overridden)
 *   2. user.name                (from the linked user account)
 *   3. 'HRMO DESIGNATE'         (hard fallback, should never reach this)
 *
 * nullOnDelete() on user_id:
 *   If a user account is deleted, the signatory row is kept (important for
 *   historical audit trails on already-printed payslips). The name/title
 *   override fields will carry the display name in that edge case.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            // Link to the system user who is the signatory.
            // nullable so existing rows don't break before they are backfilled.
            // nullOnDelete keeps the signatory row if the user is later removed.
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->nullOnDelete();

            // Make full_name nullable — it is now an optional display override.
            $table->string('full_name', 150)->nullable()->change();

            // position_title was already nullable — no change needed there.
        });
    }

    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->string('full_name', 150)->nullable(false)->change();
        });
    }
};

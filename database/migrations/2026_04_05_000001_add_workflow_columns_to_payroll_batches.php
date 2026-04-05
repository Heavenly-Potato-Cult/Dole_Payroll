<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_batches', function (Blueprint $table) {

            // ── prepared_at (submit: HR → Accountant) ─────────────────────
            if (! Schema::hasColumn('payroll_batches', 'prepared_at')) {
                $table->timestamp('prepared_at')->nullable()->after('created_by');
            }

            // ── reviewed_by / reviewed_at (certify: Accountant → RD/ARD) ──
            if (! Schema::hasColumn('payroll_batches', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()
                      ->constrained('users')->nullOnDelete()
                      ->after('prepared_at');
            }
            if (! Schema::hasColumn('payroll_batches', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            // ── approved_at (approve: RD/ARD → released) ──────────────────
            // NOTE: approved_by column + its foreign key already exist
            // from the original migration — only add approved_at
            if (! Schema::hasColumn('payroll_batches', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            // ── released_by (lock: Cashier → locked) ──────────────────────
            if (! Schema::hasColumn('payroll_batches', 'released_by')) {
                $table->foreignId('released_by')->nullable()
                      ->constrained('users')->nullOnDelete()
                      ->after('released_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['released_by']);

            $table->dropColumn([
                'prepared_at',
                'reviewed_by',
                'reviewed_at',
                'approved_at',
                'released_by',
            ]);
        });
    }
};
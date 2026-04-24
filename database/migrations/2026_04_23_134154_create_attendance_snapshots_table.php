<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_batch_id')
                  ->constrained('payroll_batches')
                  ->cascadeOnDelete();

            $table->foreignId('employee_id')
                  ->constrained('employees');

            // ── Raw data from HRIS API ───────────────────────────────────
            $table->decimal('days_present',      5, 3)->default(0)->comment('Working days actually present');
            $table->decimal('lwop_days',         5, 3)->default(0)->comment('Leave Without Pay days (after leave credits exhausted)');
            $table->unsignedSmallInteger('late_minutes')->default(0)->comment('Cumulative late minutes for the cut-off');
            $table->unsignedSmallInteger('undertime_minutes')->default(0)->comment('Cumulative undertime minutes for the cut-off');

            // ── HR correction fields ─────────────────────────────────────
            // HR reviews pulled data before computation runs.
            // If a record is wrong (e.g., missing time-in due to biometric error),
            // HR manually corrects it here. is_corrected flags it for audit.
            $table->boolean('is_corrected')->default(false);
            $table->text('correction_note')->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable();

            // ── Source tracking ──────────────────────────────────────────
            $table->enum('source', ['hris_api', 'manual'])->default('hris_api');
            $table->timestamp('fetched_at')->nullable()->comment('When the HRIS API was last called for this record');

            $table->timestamps();

            // One snapshot row per employee per batch — re-pulling overwrites via upsert
            $table->unique(['payroll_batch_id', 'employee_id'], 'uq_attendance_batch_employee');

            $table->index('employee_id');
            $table->index(['payroll_batch_id', 'is_corrected']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_snapshots');
    }
};
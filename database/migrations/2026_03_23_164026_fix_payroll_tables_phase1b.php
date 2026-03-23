<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Create payroll_entries (table never existed) ──────────────────
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')
                  ->constrained('payroll_batches')->cascadeOnDelete();
            $table->foreignId('employee_id')
                  ->constrained('employees')->restrictOnDelete();

            // Earnings
            $table->decimal('basic_salary',    12, 2)->default(0);
            $table->decimal('pera',            10, 2)->default(0);
            $table->decimal('rata',            10, 2)->default(0);
            $table->decimal('gross_income',    12, 2)->default(0);

            // Attendance deductions
            $table->decimal('lwop_days',       5, 3)->default(0);
            $table->decimal('lwop_deduction',  10, 2)->default(0);
            $table->decimal('tardiness',       10, 2)->default(0);
            $table->decimal('undertime',       10, 2)->default(0);

            // Totals
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_amount',       12, 2)->default(0);

            $table->timestamps();

            $table->unique(['payroll_batch_id', 'employee_id'], 'pe_unique');
            $table->index('payroll_batch_id');
        });

        // ── Add missing columns to payroll_deductions ─────────────────────
        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->string('code', 50)->after('deduction_type_id');
            $table->string('name', 100)->after('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');

        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->dropColumn(['code', 'name']);
        });
    }
};
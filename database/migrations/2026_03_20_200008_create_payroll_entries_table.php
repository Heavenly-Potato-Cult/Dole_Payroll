<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();

            // Salary Basis (snapshot at time of computation)
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('pera', 10, 2)->default(2000.00);
            $table->unsignedTinyInteger('salary_grade');
            $table->unsignedTinyInteger('step');

            // Attendance (from HRIS API)
            $table->decimal('days_worked', 5, 3)->default(0);
            $table->decimal('lwop_days', 5, 3)->default(0)->comment('Leave Without Pay in decimal days');
            $table->integer('tardy_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);

            // Computed Amounts
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('lwop_deduction', 10, 2)->default(0);
            $table->decimal('tardy_deduction', 10, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('withholding_tax', 10, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);

            $table->string('status', 20)->default('pending')
                  ->comment('pending, computed, locked');
            $table->boolean('is_manually_overridden')->default(false);
            $table->text('override_notes')->nullable();

            $table->timestamps();

            $table->unique(['payroll_batch_id', 'employee_id'], 'pe_unique');
            $table->index(['payroll_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};

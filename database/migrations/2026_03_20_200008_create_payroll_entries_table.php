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

    // Salary snapshot
    $table->decimal('basic_salary', 12, 2);
    $table->decimal('pera', 10, 2)->default(2000.00);
    $table->decimal('rata', 10, 2)->default(0);

    // Computed earnings
    $table->decimal('gross_income', 12, 2)->default(0);

    // Attendance deductions
    $table->decimal('lwop_days', 5, 3)->default(0);
    $table->decimal('lwop_deduction', 10, 2)->default(0);
    $table->decimal('tardiness', 10, 2)->default(0);
    $table->decimal('undertime', 10, 2)->default(0);

    // Deduction totals
    $table->decimal('total_deductions', 12, 2)->default(0);
    $table->decimal('withholding_tax', 10, 2)->default(0);
    $table->decimal('net_amount', 12, 2)->default(0);

    $table->string('status', 20)->default('computed');
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

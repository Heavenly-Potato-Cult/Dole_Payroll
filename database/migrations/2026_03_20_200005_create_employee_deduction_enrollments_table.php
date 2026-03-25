<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('employee_deduction_enrollments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->foreignId('deduction_type_id')->constrained('deduction_types')->restrictOnDelete();
    $table->decimal('amount', 12, 2)->comment('Amount per cut-off');
    $table->date('effective_from');                                          // ← was effectivity_date
    $table->date('effective_to')->nullable()->comment('null = ongoing');     // ← was end_date
    $table->boolean('is_active')->default(true);
    $table->string('remarks')->nullable();
    $table->timestamps();
    $table->unique(['employee_id', 'deduction_type_id', 'effective_from'], 'ede_unique');  // ← updated
    $table->index(['employee_id', 'is_active']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_deduction_enrollments');
    }
};

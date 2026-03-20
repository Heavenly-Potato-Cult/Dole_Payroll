<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_entry_id')->constrained('payroll_entries')->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->constrained('deduction_types')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->boolean('is_overridden')->default(false);
            $table->string('override_reason')->nullable();
            $table->timestamps();

            $table->unique(['payroll_entry_id', 'deduction_type_id'], 'pd_unique');
            $table->index('payroll_entry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_deductions');
    }
};

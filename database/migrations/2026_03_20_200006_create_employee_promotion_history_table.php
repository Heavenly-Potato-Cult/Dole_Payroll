<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_promotion_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 30)->comment('promotion, step_increment, salary_adjustment, nosi, nosa');
            $table->unsignedTinyInteger('old_salary_grade');
            $table->unsignedTinyInteger('old_step');
            $table->decimal('old_basic_salary', 12, 2);
            $table->unsignedTinyInteger('new_salary_grade');
            $table->unsignedTinyInteger('new_step');
            $table->decimal('new_basic_salary', 12, 2);
            $table->date('effectivity_date');
            $table->string('csb_no', 50)->nullable()->comment('Civil Service Bulletin No.');
            $table->string('remarks')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'effectivity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_promotion_history');
    }
};

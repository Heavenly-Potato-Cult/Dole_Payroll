<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('plantilla_item_no', 50)->unique();
            $table->string('employee_no', 30)->nullable()->unique();

            // Personal Info
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('civil_status', 20)->nullable();

            // Position Info
            $table->string('position_title');
            $table->unsignedTinyInteger('salary_grade');   // SG 1–33
            $table->unsignedTinyInteger('step');            // Step 1–8
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('pera', 10, 2)->default(2000.00)->comment('Personnel Economic Relief Allowance');

            // Assignment
            $table->foreignId('division_id')->constrained('divisions')->restrictOnDelete();
            $table->string('employment_status', 30)->default('permanent')
                  ->comment('permanent, casual, coterminous');

            // Employment Dates
            $table->date('original_appointment_date')->nullable();
            $table->date('last_promotion_date')->nullable();
            $table->date('hire_date');

            // Government IDs
            $table->string('gsis_bp_no', 30)->nullable();
            $table->string('pagibig_no', 30)->nullable();
            $table->string('philhealth_no', 30)->nullable();
            $table->string('tin', 30)->nullable();
            $table->string('sss_no', 30)->nullable();

            // Leave Credits
            $table->decimal('vacation_leave_balance', 6, 3)->default(0);
            $table->decimal('sick_leave_balance', 6, 3)->default(0);

            // Status
            $table->string('status', 20)->default('active')
                  ->comment('active, on_leave, separated, retired');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
            $table->index('division_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

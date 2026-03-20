<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tev_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tev_no', 50)->unique();
            $table->foreignId('office_order_id')->constrained('office_orders')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();

            // Track: CA = Cash Advance (before travel), Reimbursement (after travel)
            $table->string('track', 20)->default('reimbursement')
                  ->comment('cash_advance, reimbursement');

            // Travel Details
            $table->string('purpose');
            $table->string('destination');
            $table->string('travel_type', 20)->default('local');
            $table->date('travel_date_start');
            $table->date('travel_date_end');
            $table->integer('total_days')->default(0);

            // Computed Amounts
            $table->decimal('total_per_diem', 12, 2)->default(0);
            $table->decimal('total_transportation', 10, 2)->default(0);
            $table->decimal('total_other_expenses', 10, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('cash_advance_amount', 12, 2)->default(0)
                  ->comment('Amount released if CA track');
            $table->decimal('balance_due', 12, 2)->default(0)
                  ->comment('Grand total minus CA amount');

            // Status workflow
            $table->string('status', 30)->default('draft')
                  ->comment('draft, submitted, hr_approved, accountant_certified, rd_approved, cashier_released, completed');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            $table->string('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index('office_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tev_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_orders', function (Blueprint $table) {
            $table->id();
            $table->string('office_order_no', 50)->unique();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('purpose');
            $table->string('destination');
            $table->string('travel_type', 20)->default('local')
                  ->comment('local, regional, national');
            $table->date('travel_date_start');
            $table->date('travel_date_end');
            $table->string('status', 20)->default('draft')
                  ->comment('draft, approved, cancelled');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_orders');
    }
};

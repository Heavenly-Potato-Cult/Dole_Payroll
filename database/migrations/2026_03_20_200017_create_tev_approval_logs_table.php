<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tev_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tev_request_id')->constrained('tev_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('step', 50)
                  ->comment('submitted, hr_approved, accountant_certified, rd_approved, cashier_released');
            $table->string('action', 20)->comment('approved, rejected, returned');
            $table->text('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('performed_at')->useCurrent();

            $table->index(['tev_request_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tev_approval_logs');
    }
};

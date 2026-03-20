<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 50)
                  ->comment('created, computed, status_changed, entry_overridden, locked, released');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('performed_at')->useCurrent();

            // Immutable — no updated_at
            $table->index(['payroll_batch_id', 'performed_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_log');
    }
};

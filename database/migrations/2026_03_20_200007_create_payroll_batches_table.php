<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');           // 1–12
            $table->unsignedTinyInteger('cutoff');          // 1 = 1st (1-15), 2 = 2nd (16-30/31)
            $table->date('period_start');
            $table->date('period_end');
            $table->date('release_date')->nullable();

            // Status workflow: draft → computed → for_review → approved → released → locked
            $table->string('status', 30)->default('draft');

            // Approval chain tracking
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('prepared_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();

            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'cutoff'], 'payroll_batch_unique');
            $table->index(['year', 'month', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_batches');
    }
};

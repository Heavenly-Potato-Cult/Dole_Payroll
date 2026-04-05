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
        $table->unsignedSmallInteger('period_year');
        $table->unsignedTinyInteger('period_month');        // 1–12
        $table->string('cutoff', 5);                        // '1st' or '2nd'
        $table->date('period_start');
        $table->date('period_end');
        $table->date('release_date')->nullable();

        // Status workflow: draft → computed → pending_accountant → pending_rd → released → locked
        $table->string('status', 30)->default('draft');

        // Approval chain tracking
$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
$table->timestamp('prepared_at')->nullable();        // submit()
$table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
$table->timestamp('reviewed_at')->nullable();        // certify()
$table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
$table->timestamp('approved_at')->nullable();        // approve()
$table->timestamp('released_at')->nullable();        // approve()
$table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();

        $table->string('remarks')->nullable();

        $table->softDeletes();
        $table->timestamps();

        $table->unique(['period_year', 'period_month', 'cutoff'], 'payroll_batch_unique');
        $table->index(['period_year', 'period_month', 'status']);
    });
}

    public function down(): void
    {
        Schema::dropIfExists('payroll_batches');
    }
};

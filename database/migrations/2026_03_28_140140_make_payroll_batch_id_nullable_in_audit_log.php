<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('payroll_audit_log', function (Blueprint $table) {
        $table->unsignedBigInteger('payroll_batch_id')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('payroll_audit_log', function (Blueprint $table) {
        $table->unsignedBigInteger('payroll_batch_id')->nullable(false)->change();
    });
}
};

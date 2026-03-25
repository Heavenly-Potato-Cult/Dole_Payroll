<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // payroll_entries already exists — only add missing columns if needed
        Schema::table('payroll_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_entries', 'rata')) {
                $table->decimal('rata', 10, 2)->default(0)->after('pera');
            }
            if (!Schema::hasColumn('payroll_entries', 'gross_income')) {
                $table->decimal('gross_income', 12, 2)->default(0)->after('rata');
            }
            if (!Schema::hasColumn('payroll_entries', 'lwop_days')) {
                $table->decimal('lwop_days', 5, 3)->default(0)->after('gross_income');
            }
            if (!Schema::hasColumn('payroll_entries', 'lwop_deduction')) {
                $table->decimal('lwop_deduction', 10, 2)->default(0)->after('lwop_days');
            }
            if (!Schema::hasColumn('payroll_entries', 'tardiness')) {
                $table->decimal('tardiness', 10, 2)->default(0)->after('lwop_deduction');
            }
            if (!Schema::hasColumn('payroll_entries', 'undertime')) {
                $table->decimal('undertime', 10, 2)->default(0)->after('tardiness');
            }
        });

        // Add missing columns to payroll_deductions
        Schema::table('payroll_deductions', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_deductions', 'code')) {
                $table->string('code', 50)->after('deduction_type_id');
            }
            if (!Schema::hasColumn('payroll_deductions', 'name')) {
                $table->string('name', 100)->after('code');
            }
        });
    }

public function down(): void
{
    Schema::table('payroll_entries', function (Blueprint $table) {
        $cols = ['rata', 'gross_income', 'lwop_days', 'lwop_deduction', 'tardiness', 'undertime'];
        $existing = array_filter($cols, fn($col) => Schema::hasColumn('payroll_entries', $col));
        if ($existing) {
            $table->dropColumn(array_values($existing));
        }
    });

    Schema::table('payroll_deductions', function (Blueprint $table) {
        $cols = ['code', 'name'];
        $existing = array_filter($cols, fn($col) => Schema::hasColumn('payroll_deductions', $col));
        if ($existing) {
            $table->dropColumn(array_values($existing));
        }
    });
}
};
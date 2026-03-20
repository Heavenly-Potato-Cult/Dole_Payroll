<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('e.g. PAGIBIG1, GSIS_MPL, WHT');
            $table->string('name');
            $table->string('short_name', 50)->nullable();
            $table->string('category', 30)
                  ->comment('mandatory, loan, voluntary, tax, union');
            $table->boolean('is_fixed_amount')->default(false)
                  ->comment('true = fixed peso, false = percentage or variable');
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->unsignedSmallInteger('display_order')->default(0)
                  ->comment('Order on payslip per DOLE RO9 standard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_types');
    }
};

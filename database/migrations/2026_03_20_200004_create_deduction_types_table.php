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
        $table->string('code', 50)->unique();
        $table->string('name', 200);
        $table->string('short_name', 50)->nullable();
        $table->string('category', 50)->default('misc');
        $table->boolean('is_computed')->default(false)
              ->comment('true = auto-calculated by payroll engine');
        $table->boolean('is_fixed_amount')->default(false);
        $table->decimal('default_amount', 12, 2)->nullable();
        $table->unsignedSmallInteger('display_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->text('notes')->nullable();
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

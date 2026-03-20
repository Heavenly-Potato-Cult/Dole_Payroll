<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('per_diem_rates', function (Blueprint $table) {
            $table->id();
            $table->string('travel_type', 20)->comment('local, regional, national');
            $table->string('destination_category', 50)->nullable()
                  ->comment('e.g. Metro Manila, Regional Center, Others');
            $table->unsignedSmallInteger('year');
            $table->decimal('daily_rate', 10, 2)->comment('Full day per diem per COA Circular');
            $table->decimal('half_day_rate', 10, 2)->nullable();
            $table->string('coa_circular_ref', 50)->nullable()
                  ->comment('e.g. COA Circular 2021-001');
            $table->timestamps();

            $table->unique(['travel_type', 'destination_category', 'year'], 'pdr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('per_diem_rates');
    }
};

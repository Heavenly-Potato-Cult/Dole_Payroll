<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_index_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('salary_grade');   // 1–33
            $table->unsignedTinyInteger('step');            // 1–8
            $table->unsignedSmallInteger('year');           // e.g. 2021, 2022
            $table->decimal('monthly_salary', 12, 2);
            $table->timestamps();

            $table->unique(['salary_grade', 'step', 'year'], 'sit_unique');
            $table->index(['salary_grade', 'step', 'year'], 'sit_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_index_tables');
    }
};

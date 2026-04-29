<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove unique constraint from plantilla_item_no
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_plantilla_item_no_unique');
        });
    }

    public function down(): void
    {
        // Re-add the unique constraint if needed
        Schema::table('employees', function (Blueprint $table) {
            $table->unique('plantilla_item_no');
        });
    }
};

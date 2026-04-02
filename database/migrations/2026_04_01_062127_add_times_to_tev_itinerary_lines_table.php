<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('tev_itinerary_lines', function (Blueprint $table) {
        $table->time('departure_time')->nullable()->after('destination');
        $table->time('arrival_time')->nullable()->after('departure_time');
    });
}

public function down(): void
{
    Schema::table('tev_itinerary_lines', function (Blueprint $table) {
        $table->dropColumn(['departure_time', 'arrival_time']);
    });
}
};

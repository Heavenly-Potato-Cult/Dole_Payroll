<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tev_itinerary_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tev_request_id')->constrained('tev_requests')->cascadeOnDelete();
            $table->date('travel_date');
            $table->string('origin');
            $table->string('destination');
            $table->string('mode_of_transport', 50)->nullable()
                  ->comment('bus, jeepney, boat, plane, vehicle');
            $table->decimal('transportation_cost', 10, 2)->default(0);
            $table->decimal('per_diem_amount', 10, 2)->default(0)
                  ->comment('From per_diem_rates lookup');
            $table->boolean('is_half_day')->default(false);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index(['tev_request_id', 'travel_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tev_itinerary_lines');
    }
};

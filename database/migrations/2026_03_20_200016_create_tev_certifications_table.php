<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tev_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tev_request_id')->constrained('tev_requests')->cascadeOnDelete();

            // Certificate of Travel Completed
            $table->date('date_returned')->nullable();
            $table->string('place_reported_back', 100)->nullable();
            $table->boolean('travel_completed')->default(false);

            // Annex A — Expenses Not Requiring Receipts
            $table->decimal('annex_a_amount', 10, 2)->default(0);
            $table->text('annex_a_particulars')->nullable();

            // Certificate of Appearance
            $table->string('agency_visited')->nullable();
            $table->date('appearance_date')->nullable();
            $table->string('contact_person')->nullable();

            $table->foreignId('certified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('certified_at')->nullable();
            $table->timestamps();

            $table->unique('tev_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tev_certifications');
    }
};

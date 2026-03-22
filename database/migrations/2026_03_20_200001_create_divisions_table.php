<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->unique();
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Seed the four official DOLE RO9 divisions ────────────
        DB::table('divisions')->insert([
            [
                'name'        => 'Office of the Regional Director',
                'code'        => 'ORD',
                'description' => 'Office of the Regional Director, DOLE Regional Office IX',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Internal Management Services Division',
                'code'        => 'IMSD',
                'description' => 'Handles administrative, finance, and human resource functions.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Technical Support & Services Division',
                'code'        => 'TSSD',
                'description' => 'Handles labor standards, employment facilitation, and HRIS.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Labor Laws Compliance Division',
                'code'        => 'LLCD',
                'description' => 'Labor inspectorate and compliance monitoring.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
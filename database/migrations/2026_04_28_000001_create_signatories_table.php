<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatories', function (Blueprint $table) {
            $table->id();

            // e.g. 'hrmo_designate', 'accountant', 'ard', 'cashier'
            // Kept as a plain string so you can add new role types without
            // changing the schema — just insert a new row.
            $table->string('role_type', 60)->index();

            $table->string('full_name', 150);

            // e.g. "Labor Employment Officer III"
            $table->string('position_title', 150)->nullable();

            // Only one signatory per role_type should be active at a time.
            // The SignatoryController::toggle() method enforces this by
            // deactivating all other rows for the same role_type before
            // activating the selected one.
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });

        // ── Seed the current HRMO Designate ─────────────────────────────
        // This gives the system a working value immediately after migration.
        // Update via Admin → Signatories when the designate changes.
        DB::table('signatories')->insert([
            'role_type'      => 'hrmo_designate',
            'full_name'      => 'Aira D. Lagradilla',
            'position_title' => 'Labor Employment Officer III',
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('signatories');
    }
};

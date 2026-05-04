<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('deduction_types')
            ->where('id', 7)
            ->where('code', 'GSIS_LIFE_RET')  // safety check — won't run if id/code mismatch
            ->update([
                'is_active' => false,
                'notes'     => 'DEPRECATED — superseded by GSIS_LIFE_RETIREMENT (id=32). Do not use.',
            ]);
    }

    public function down(): void
    {
        // Rollback restores the original state
        DB::table('deduction_types')
            ->where('id', 7)
            ->where('code', 'GSIS_LIFE_RET')
            ->update([
                'is_active' => true,
                'notes'     => 'Mandatory GSIS contribution. Computed: 9% of basic salary (employee share). Mandatory for permanent employees.',
            ]);
    }
};

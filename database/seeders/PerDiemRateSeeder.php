<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerDiemRateSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate first so re-running is safe
        DB::table('per_diem_rates')->truncate();

        $year = 2024; // COA Circular No. 2004-006 rates (still current as of 2024)

        $rates = [
            [
                'travel_type'          => 'local',
                'destination_category' => 'Within the region',
                'year'                 => $year,
                'daily_rate'           => 800.00,
                'half_day_rate'        => 400.00,
                'coa_circular_ref'     => 'COA Circular No. 2004-006',
            ],
            [
                'travel_type'          => 'regional',
                'destination_category' => 'To another region within Mindanao/Visayas/Luzon',
                'year'                 => $year,
                'daily_rate'           => 1000.00,
                'half_day_rate'        => 500.00,
                'coa_circular_ref'     => 'COA Circular No. 2004-006',
            ],
            [
                'travel_type'          => 'national',
                'destination_category' => 'To National Capital Region or other island group',
                'year'                 => $year,
                'daily_rate'           => 1500.00,
                'half_day_rate'        => 750.00,
                'coa_circular_ref'     => 'COA Circular No. 2004-006',
            ],
        ];

        foreach ($rates as $rate) {
            DB::table('per_diem_rates')->insert(array_merge($rate, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ Per diem rates seeded: local ₱800 | regional ₱1,000 | national ₱1,500');
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding production data from existing database...');
        
        // Get all table data before migration
        $tables = [
            'employees',
            'employee_deduction_enrollments', 
            'employee_promotion_history',
            'payroll_batches',
            'payroll_entries',
            'payroll_deductions',
            'payroll_audit_log',
            'special_payroll_batches',
            'office_orders',
            'tev_requests',
            'tev_itinerary_lines',
            'tev_certifications',
            'tev_approval_logs'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $data = DB::table($table)->get();
                
                if ($data->isNotEmpty()) {
                    $this->command->info("Backing up {$data->count()} records from {$table}");
                    
                    // Store data in a temporary file for restoration
                    $jsonData = $data->toJson();
                    $filename = database_path("seeders/data/{$table}_data.json");
                    
                    // Ensure directory exists
                    $dir = dirname($filename);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    
                    file_put_contents($filename, $jsonData);
                }
            }
        }
        
        $this->command->info('Production data backup completed!');
    }
    
    /**
     * Restore production data from backup files
     */
    public function restore(): void
    {
        $this->command->info('Restoring production data...');
        
        $tables = [
            'employees',
            'employee_deduction_enrollments', 
            'employee_promotion_history',
            'payroll_batches',
            'payroll_entries',
            'payroll_deductions',
            'payroll_audit_log',
            'special_payroll_batches',
            'office_orders',
            'tev_requests',
            'tev_itinerary_lines',
            'tev_certifications',
            'tev_approval_logs'
        ];

        foreach ($tables as $table) {
            $filename = database_path("seeders/data/{$table}_data.json");
            
            if (file_exists($filename)) {
                $jsonData = file_get_contents($filename);
                $data = json_decode($jsonData, true);
                
                if (!empty($data)) {
                    // Clear existing data
                    DB::table($table)->truncate();
                    
                    // Insert data with auto-increment handling
                    foreach ($data as $record) {
                        // Remove auto-increment primary key if present
                        $record = $this->removeAutoIncrementKey($table, $record);
                        
                        DB::table($table)->insert($record);
                    }
                    
                    $this->command->info("Restored " . count($data) . " records to {$table}");
                }
            }
        }
        
        $this->command->info('Production data restoration completed!');
    }
    
    /**
     * Remove auto-increment primary key from record
     */
    private function removeAutoIncrementKey(string $table, array $record): array
    {
        $autoIncrementKeys = [
            'employees' => 'id',
            'employee_deduction_enrollments' => 'id',
            'employee_promotion_history' => 'id',
            'payroll_batches' => 'id',
            'payroll_entries' => 'id',
            'payroll_deductions' => 'id',
            'payroll_audit_log' => 'id',
            'special_payroll_batches' => 'id',
            'office_orders' => 'id',
            'tev_requests' => 'id',
            'tev_itinerary_lines' => 'id',
            'tev_certifications' => 'id',
            'tev_approval_logs' => 'id',
        ];
        
        if (isset($autoIncrementKeys[$table])) {
            unset($record[$autoIncrementKeys[$table]]);
        }
        
        return $record;
    }
}

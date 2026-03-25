<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run role seeder first
        $this->call(RoleSeeder::class);

        $this->call(SalaryIndexTableSeeder::class);
        $this->call(DeductionTypeSeeder::class);
        $this->call(EmployeeSeeder::class);

        

        // Create default payroll admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@dole9.gov.ph'],
            [
                'name'              => 'Payroll Admin',
                'email'             => 'admin@dole9.gov.ph',
                'password'          => Hash::make('Admin@DOLE9!'),
                'email_verified_at' => now(),
            ]
        );

        // Assign payroll_officer role
        $admin->assignRole('payroll_officer');

        $this->command->info('✅ Default admin created: admin@dole9.gov.ph');
        $this->command->info('✅ Password: Admin@DOLE9!');
        $this->command->warn('⚠️  Change this password immediately after first login!');
    }
}
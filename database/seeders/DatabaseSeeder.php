<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run role seeder first (roles must exist before assigning them)
        $this->call(RoleSeeder::class);
        $this->call(SalaryIndexTableSeeder::class);
        $this->call(DeductionTypeSeeder::class);
        $this->call(EmployeeSeeder::class);

        // ── Production Admin ─────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@dole9.gov.ph'],
            [
                'name'              => 'Payroll Admin',
                'password'          => Hash::make('Admin@DOLE9!'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['payroll_officer']);

        $this->command->info('✅ Admin created: admin@dole9.gov.ph / Admin@DOLE9!');
        $this->command->warn('⚠️  Change the admin password immediately after first login!');

        // ── Test / Development Accounts ───────────────────────────────────────
        // These are safe dummy accounts for local + staging testing.
        // All use the same password: Test@DOLE9!
        // firstOrCreate ensures re-running the seeder won't duplicate them.
        $testUsers = [
            [
                'name'  => 'Test HRMO',
                'email' => 'hrmo@dole9.gov.ph',
                'role'  => 'hrmo',
            ],
            [
                'name'  => 'Test Accountant',
                'email' => 'accountant@dole9.gov.ph',
                'role'  => 'accountant',
            ],
            [
                'name'  => 'Test Budget Officer',
                'email' => 'budget@dole9.gov.ph',
                'role'  => 'budget_officer',
            ],
            [
                'name'  => 'Test Chief Admin Officer',
                'email' => 'cao@dole9.gov.ph',
                'role'  => 'chief_admin_officer',
            ],
            [
                'name'  => 'Test ARD',
                'email' => 'ard@dole9.gov.ph',
                'role'  => 'ard',
            ],
            [
                'name'  => 'Test Cashier',
                'email' => 'cashier@dole9.gov.ph',
                'role'  => 'cashier',
            ],
        ];

        foreach ($testUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('Test@DOLE9!'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$data['role']]);
            $this->command->info("✅ Test user: {$data['email']} → {$data['role']}");
        }

        $this->command->warn('⚠️  Test accounts use password: Test@DOLE9!');
        $this->command->warn('⚠️  Remove or disable test accounts before production launch!');
    }
}
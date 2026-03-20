<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;        // ← was Spatie\LaravelPermission\Models\Role
use Spatie\Permission\Models\Permission;  // ← was Spatie\LaravelPermission\Models\Permission

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();  // ← fixed namespace

        $roles = [
            'payroll_officer',
            'hrmo',
            'accountant',
            'budget_officer',
            'chief_admin_officer',
            'ard',
            'cashier',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );
        }

        $this->command->info('✅ Roles created: ' . implode(', ', $roles));
    }
}
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG AUTH ROLES ===\n";

// Check if user is authenticated
if (!Auth::check()) {
    echo "User is NOT authenticated!\n";
    exit;
}

$user = Auth::user();
echo "Authenticated User ID: {$user->id}\n";
echo "User Name: {$user->name}\n";
echo "User Email: " . ($user->email ?? 'NULL') . "\n";
echo "Employee ID: " . ($user->employee_id ?? 'NULL') . "\n";

if ($user->employee) {
    echo "Employee Name: {$user->employee->first_name} {$user->employee->last_name}\n";
    echo "Employee No: {$user->employee->employee_no}\n";
}

echo "\n=== ROLES ===\n";
$roles = $user->getRoleNames();
echo "All Roles: " . $roles->implode(', ') . "\n";
echo "First Role: " . ($roles->first() ?? 'NULL') . "\n";

echo "\n=== ROLE CHECKS ===\n";
echo "Has payroll_officer: " . ($user->hasRole('payroll_officer') ? 'YES' : 'NO') . "\n";
echo "Has employee: " . ($user->hasRole('employee') ? 'YES' : 'NO') . "\n";
echo "Has super_admin: " . ($user->hasRole('super_admin') ? 'YES' : 'NO') . "\n";

echo "\n=== SESSION ===\n";
echo "HRIS Employee ID: " . (session('hris_employee_id') ?? 'NULL') . "\n";
echo "HRIS User Data: " . (session('hris_user') ? 'EXISTS' : 'NULL') . "\n";

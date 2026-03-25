<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $divisionId = DB::table('divisions')->value('id'); // grab first division

        $employees = [
            [
                'plantilla_item_no' => 'DOLE9-001',
                'last_name'         => 'SANTOS',
                'first_name'        => 'MARIA',
                'middle_name'       => 'REYES',
                'position_title'    => 'Administrative Aide IV',
                'salary_grade'      => 4,
                'step'              => 1,
                'sit_year'          => 2022,
                'basic_salary'      => 14993.00,
                'pera'              => 2000.00,
                'division_id'       => $divisionId,
                'hire_date'         => '2015-06-01',
                'status'            => 'active',
                'employment_status' => 'permanent',
                'tin'               => '111-222-333-000',
                'gsis_bp_no'        => 'GSIS-001',
                'pagibig_no'        => 'PAGIBIG-001',
                'philhealth_no'     => 'PH-001',
            ],
            [
                'plantilla_item_no' => 'DOLE9-002',
                'last_name'         => 'DELA CRUZ',
                'first_name'        => 'JUAN',
                'middle_name'       => 'GARCIA',
                'position_title'    => 'Labor and Employment Officer II',
                'salary_grade'      => 15,
                'step'              => 3,
                'sit_year'          => 2022,
                'basic_salary'      => 35858.00,
                'pera'              => 2000.00,
                'division_id'       => $divisionId,
                'hire_date'         => '2010-03-15',
                'status'            => 'active',
                'employment_status' => 'permanent',
                'tin'               => '111-222-333-001',
                'gsis_bp_no'        => 'GSIS-002',
                'pagibig_no'        => 'PAGIBIG-002',
                'philhealth_no'     => 'PH-002',
            ],
            [
                'plantilla_item_no' => 'DOLE9-003',
                'last_name'         => 'MENDOZA',
                'first_name'        => 'ANA',
                'middle_name'       => 'LUNA',
                'position_title'    => 'Labor and Employment Officer III',
                'salary_grade'      => 18,
                'step'              => 2,
                'sit_year'          => 2022,
                'basic_salary'      => 45706.00,
                'pera'              => 2000.00,
                'division_id'       => $divisionId,
                'hire_date'         => '2008-01-10',
                'status'            => 'active',
                'employment_status' => 'permanent',
                'tin'               => '111-222-333-002',
                'gsis_bp_no'        => 'GSIS-003',
                'pagibig_no'        => 'PAGIBIG-003',
                'philhealth_no'     => 'PH-003',
            ],
        ];

        foreach ($employees as $emp) {
            DB::table('employees')->insertOrIgnore(array_merge($emp, [
                'vacation_leave_balance' => 15.000,
                'sick_leave_balance'     => 15.000,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]));
        }

        $this->command->info('EmployeeSeeder: ' . count($employees) . ' test employees inserted.');
    }
}
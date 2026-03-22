<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeductionTypeSeeder extends Seeder
{
    /**
     * Seed all 28 deduction types for DOLE RO9 payroll.
     *
     * Sources:
     *   - Payroll_and_Work_Conversion_Reference.pdf (official list)
     *   - 01A-General-Payroll-Monthly.xls (column headers)
     *   - DOLE_Payroll_System_Manuscript_MONOLITH.docx
     *
     * is_computed = true  → system calculates the amount automatically
     *                        (PhilHealth, GSIS Life/Ret, WHT, PAG-IBIG I)
     * is_computed = false → HR/Payroll officer enters amount manually
     *                        (all loan amortizations, HMO, etc.)
     *
     * Run: php artisan db:seed --class=DeductionTypeSeeder
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('deduction_types')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('deduction_types')->insert([

            // ── PAG-IBIG / HDMF ──────────────────────────────────
            [
                'code'          => 'PAGIBIG_1',
                'name'          => 'PAG-IBIG I',
                'display_order' => 1,
                'category'      => 'pagibig',
                'is_computed'   => true,
                'is_active'     => true,
                'notes'         => 'Mandatory HDMF contribution. Computed: 2% of basic salary, max ₱100.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'HDMF_MPL',
                'name'          => 'HDMF Multi-Purpose Loan',
                'display_order' => 2,
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — individual loan amortization amount.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'HDMF_CALAMITY',
                'name'          => 'HDMF Calamity Loan',
                'display_order' => 3,
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — individual calamity loan amortization.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'HDMF_HOUSING',
                'name'          => 'HDMF House & Lot',
                'display_order' => 4,
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — housing loan amortization.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'PAGIBIG_2',
                'name'          => 'PAG-IBIG II',
                'display_order' => 5,
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Voluntary additional PAG-IBIG contribution. Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],

            // ── PhilHealth ────────────────────────────────────────
            [
                'code'          => 'PHILHEALTH',
                'name'          => 'PhilHealth',
                'display_order' => 6,
                'category'      => 'philhealth',
                'is_computed'   => true,
                'is_active'     => true,
                'notes'         => 'Computed: 5% of basic salary ÷ 2 (employee share, semi-monthly). Per PhilHealth circular.',
                'created_at'    => now(), 'updated_at' => now(),
            ],

            // ── GSIS ──────────────────────────────────────────────
            [
                'code'          => 'GSIS_LIFE_RET',
                'name'          => 'GSIS Life/Retirement',
                'display_order' => 7,
                'category'      => 'gsis',
                'is_computed'   => true,
                'is_active'     => true,
                'notes'         => 'Computed: 9% of basic salary (employee share). Mandatory for permanent employees.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_CONSO',
                'name'          => 'GSIS Conso Loan',
                'display_order' => 8,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — consolidated loan amortization.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_POLICY',
                'name'          => 'GSIS Policy Loan',
                'display_order' => 9,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_REALESTATE',
                'name'          => 'GSIS Real Estate',
                'display_order' => 10,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — real estate loan amortization.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_MPL',
                'name'          => 'GSIS MPL',
                'display_order' => 11,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — GSIS Multi-Purpose Loan.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_CPL',
                'name'          => 'GSIS CPL',
                'display_order' => 12,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — Consolidated Policy Loan.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_MPL_LITE',
                'name'          => 'GSIS MPL Lite',
                'display_order' => 13,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_GFAL',
                'name'          => 'GSIS GFAL',
                'display_order' => 14,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — GSIS Financial Assistance Loan.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_HELP',
                'name'          => 'GSIS HELP',
                'display_order' => 15,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — GSIS Housing Emergency Loan Program.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_EMERG',
                'name'          => 'GSIS Emergency Loan',
                'display_order' => 16,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'GSIS_EDUC',
                'name'          => 'GSIS Educ Loan',
                'display_order' => 17,
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — GSIS Educational Loan.',
                'created_at'    => now(), 'updated_at' => now(),
            ],

            // ── Other Government ──────────────────────────────────
            [
                'code'          => 'MASS',
                'name'          => 'MASS',
                'display_order' => 18,
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Mutual Aid Support System. Manual entry — fixed amount per employee.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'SSS',
                'name'          => 'SSS Contribution',
                'display_order' => 19,
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'For employees with prior private sector service. Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'PROVIDENT',
                'name'          => 'Provident Fund',
                'display_order' => 20,
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'DOLE Provident Fund contribution. Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'WHT',
                'name'          => 'W/Holding Tax',
                'display_order' => 21,
                'category'      => 'other_gov',
                'is_computed'   => true,
                'is_active'     => true,
                'notes'         => 'Computed: based on BIR tax table (TRAIN Law). Uses annual gross projected from monthly salary.',
                'created_at'    => now(), 'updated_at' => now(),
            ],

            // ── Loans ─────────────────────────────────────────────
            [
                'code'          => 'LBP_LOAN',
                'name'          => 'LBP Loan',
                'display_order' => 22,
                'category'      => 'loan',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Land Bank of the Philippines salary loan. Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'HMO',
                'name'          => 'HMO',
                'display_order' => 23,
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Health Maintenance Organization premium. Manual entry.',
                'created_at'    => now(), 'updated_at' => now(),
            ],

            // ── CARESS IX ─────────────────────────────────────────
            [
                'code'          => 'CARESS_UNION',
                'name'          => 'CARESS IX Union Dues',
                'display_order' => 24,
                'category'      => 'caress',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — fixed union dues per member.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'CARESS_MORTUARY',
                'name'          => 'CARESS IX Mortuary',
                'display_order' => 25,
                'category'      => 'caress',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — mortuary fund contribution.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'CARESS_LOAN',
                'name'          => 'CARESS IX CAREs Loan',
                'display_order' => 26,
                'category'      => 'caress',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — CARESS cooperative loan amortization.',
                'created_at'    => now(), 'updated_at' => now(),
            ],

            // ── Miscellaneous ─────────────────────────────────────
            [
                'code'          => 'SMART_EXCESS',
                'name'          => 'Smart Plan Gold Excess',
                'display_order' => 27,
                'category'      => 'misc',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Manual entry — excess charges on Smart Plan Gold.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
            [
                'code'          => 'REFUND',
                'name'          => 'Refund (Various)',
                'display_order' => 28,
                'category'      => 'misc',
                'is_computed'   => false,
                'is_active'     => true,
                'notes'         => 'Cash advance refunds, BTR disallowances, etc. Manual entry per payroll period.',
                'created_at'    => now(), 'updated_at' => now(),
            ],
        ]);

        $this->command->info('DeductionTypeSeeder: 28 deduction types inserted.');
    }
}
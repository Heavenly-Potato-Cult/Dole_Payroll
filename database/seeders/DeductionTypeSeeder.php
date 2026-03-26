<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DeductionTypeSeeder
 *
 * Seeds all 30 deduction types for the DOLE RO9 payroll system.
 *
 * ── CRITICAL CONTRACT ────────────────────────────────────────────────────────
 *
 * The `code` values here are the single source of truth. They MUST match:
 *   1. DeductionService::resolveDeductions()       — computed[] map keys
 *   2. PayrollEntryController::payslipRowDefinitions() — 'code' field in each row
 *   3. employee_deduction_enrollments.deduction_type_id — via code lookup
 *
 * DO NOT rename codes without updating all three locations above.
 *
 * ── is_computed flag ─────────────────────────────────────────────────────────
 *
 * TRUE  = amount is auto-calculated from salary (PAG-IBIG I, PhilHealth,
 *         GSIS Life/Retirement, Withholding Tax). No enrollment needed.
 *
 * FALSE = amount comes from employee_deduction_enrollments. The payroll
 *         officer enrolls these per employee with the fixed monthly amount.
 *
 * ── display_order ────────────────────────────────────────────────────────────
 *
 * Matches the exact payslip column order from the DOLE RO9 Excel template
 * (01A-General-Payroll-Monthly.xlsx, Payslip sheet).
 *
 * ── Re-running safely ────────────────────────────────────────────────────────
 *
 * Uses updateOrInsert keyed on `code` so this can be re-run without duplicates.
 * To fully reset: php artisan migrate:fresh --seed
 */
class DeductionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $types = [
            // ── HDMF / Pag-IBIG ─────────────────────────────────────────────
            [
                'code'          => 'PAG_IBIG_1',
                'name'          => 'PAG-IBIG I',
                'category'      => 'pagibig',
                'is_computed'   => true,   // 2% of basic, max ₱100/month EE share
                'is_active'     => true,
                'display_order' => 1,
                'notes'         => 'HDMF mandatory contribution. EE: 2% of basic (cap ₱100/mo). Auto-computed.',
            ],
            [
                'code'          => 'HDMF_MPL',
                'name'          => 'MULTI-PURPOSE',
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 2,
                'notes'         => 'HDMF Multi-Purpose Loan. Fixed monthly amortization per employee.',
            ],
            [
                'code'          => 'HDMF_CAL',
                'name'          => 'CALAMITY LOAN',
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 3,
                'notes'         => 'HDMF Calamity Loan amortization.',
            ],
            [
                'code'          => 'HDMF_HOUSING',
                'name'          => 'HOUSE & LOT',
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 4,
                'notes'         => 'HDMF Housing / House & Lot loan amortization.',
            ],
            [
                'code'          => 'HDMF_P2',
                'name'          => 'PAG-IBIG II',
                'category'      => 'pagibig',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 5,
                'notes'         => 'Modified Pag-IBIG II (MP2) voluntary savings. Fixed enrollment amount.',
            ],

            // ── PhilHealth ───────────────────────────────────────────────────
            [
                'code'          => 'PHILHEALTH',
                'name'          => 'PHILHEALTH',
                'category'      => 'philhealth',
                'is_computed'   => true,   // 5% of basic, EE share = 50%, floor ₱500 ceiling ₱5000/mo
                'is_active'     => true,
                'display_order' => 6,
                'notes'         => 'PhilHealth mandatory contribution. EE: 50% of 5% premium. Auto-computed.',
            ],

            // ── GSIS ─────────────────────────────────────────────────────────
            [
                'code'          => 'GSIS_LIFE_RETIREMENT',
                'name'          => 'LIFE/RETIREMENT',
                'category'      => 'gsis',
                'is_computed'   => true,   // 9% of basic monthly (Personal Share)
                'is_active'     => true,
                'display_order' => 7,
                'notes'         => 'GSIS Life & Retirement Personal Share (PS). 9% of basic. Auto-computed.',
            ],
            [
                'code'          => 'GSIS_CONSO',
                'name'          => 'CONSO LOAN',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 8,
                'notes'         => 'GSIS Consolidated Loan amortization.',
            ],
            [
                'code'          => 'GSIS_POLICY',
                'name'          => 'POLICY LOAN',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 9,
                'notes'         => 'GSIS Policy Loan (Regular & Optional).',
            ],
            [
                'code'          => 'GSIS_REAL_ESTATE',
                'name'          => 'REAL ESTATE',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 10,
                'notes'         => 'GSIS Real Estate Loan amortization.',
            ],
            [
                'code'          => 'GSIS_MPL',
                'name'          => 'GSIS MPL',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 11,
                'notes'         => 'GSIS Multi-Purpose Loan (MPL).',
            ],
            [
                'code'          => 'GSIS_CPL',
                'name'          => 'GSIS CPL',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 12,
                'notes'         => 'GSIS Consolidated Policy Loan (CPL).',
            ],
            [
                'code'          => 'GSIS_MPL_LITE',
                'name'          => 'GSIS MPL Lite',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 13,
                'notes'         => 'GSIS MPL Lite.',
            ],
            [
                'code'          => 'GSIS_GFAL',
                'name'          => 'GFAL',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 14,
                'notes'         => 'GSIS GFAL (Gratuity Fund Assistance Loan).',
            ],
            [
                'code'          => 'GSIS_HELP',
                'name'          => 'HELP',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 15,
                'notes'         => 'GSIS HELP (Housing Emergency Loan Program).',
            ],
            [
                'code'          => 'GSIS_EMERGENCY',
                'name'          => 'GSIS EMERG LOAN',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 16,
                'notes'         => 'GSIS Emergency Loan.',
            ],

            // ── Other Government / Voluntary ──────────────────────────────────
            [
                'code'          => 'MASS',
                'name'          => 'MASS',
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 17,
                'notes'         => 'MASS (Mutual Aid Support System). Fixed enrollment amount.',
            ],
            [
                'code'          => 'SSS',
                'name'          => 'SSS CONTRIBUTION',
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 18,
                'notes'         => 'SSS Voluntary Contribution. Fixed enrollment amount.',
            ],
            [
                'code'          => 'PROVIDENT_FUND',
                'name'          => 'PROVIDENT FUND',
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 19,
                'notes'         => 'DOLE Provident Fund contribution. Fixed enrollment amount.',
            ],

            // ── Tax ───────────────────────────────────────────────────────────
            [
                'code'          => 'WITHHOLDING_TAX',
                'name'          => 'W/HOLDING TAX',
                'category'      => 'other_gov',
                'is_computed'   => true,   // BIR TRAIN Law annualized method
                'is_active'     => true,
                'display_order' => 20,
                'notes'         => 'Withholding Tax. BIR TRAIN Law graduated table, annualized. Auto-computed.',
            ],

            // ── Loans ─────────────────────────────────────────────────────────
            [
                'code'          => 'LBP_LOAN',
                'name'          => 'LBP LOAN',
                'category'      => 'loan',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 21,
                'notes'         => 'Land Bank of the Philippines salary loan amortization.',
            ],
            [
                'code'          => 'GSIS_EDUC',
                'name'          => 'GSIS EDUCL LOAN',
                'category'      => 'gsis',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 22,
                'notes'         => 'GSIS Educational Assistance Loan.',
            ],
            [
                'code'          => 'HMO',
                'name'          => 'HMO',
                'category'      => 'other_gov',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 23,
                'notes'         => 'HMO (Health Maintenance Organization) deduction.',
            ],

            // ── CARESS IX ────────────────────────────────────────────────────
            [
                'code'          => 'CARESS_UNION',
                'name'          => 'UNION DUES',
                'category'      => 'caress',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 24,
                'notes'         => 'CARESS IX Union Dues. Fixed monthly amount.',
            ],
            [
                'code'          => 'CARESS_MORTUARY',
                'name'          => 'MORTUARY',
                'category'      => 'caress',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 25,
                'notes'         => 'CARESS IX Mortuary contribution. = Daily Rate × 25%.',
            ],
            [
                'code'          => 'CARESS_CARES',
                'name'          => 'CAREs',
                'category'      => 'caress',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 26,
                'notes'         => 'CARESS IX CAREs Loan amortization.',
            ],

            // ── Miscellaneous ─────────────────────────────────────────────────
            [
                'code'          => 'SMART_PLAN_GOLD',
                'name'          => 'SMART PLAN GOLD EXCESS CHARGES',
                'category'      => 'misc',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 27,
                'notes'         => 'Smart Plan Gold excess charges deduction.',
            ],
            [
                'code'          => 'REFUND_VARIOUS',
                'name'          => 'REFUND (VARIOUS)',
                'category'      => 'misc',
                'is_computed'   => false,
                'is_active'     => true,
                'display_order' => 28,
                'notes'         => 'Refund / BTR Refund — various types. Per-payroll enrollment.',
            ],
        ];

        foreach ($types as $type) {
            DB::table('deduction_types')->updateOrInsert(
                ['code' => $type['code']],   // match key
                array_merge($type, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        $this->command->info('DeductionTypeSeeder: ' . count($types) . ' deduction types seeded/updated.');
        $this->command->info('Computed types: PAG_IBIG_1, PHILHEALTH, GSIS_LIFE_RETIREMENT, WITHHOLDING_TAX');
    }
}
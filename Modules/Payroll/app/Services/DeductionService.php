<?php

namespace Modules\Payroll\Services;

use Modules\Payroll\Models\DeductionType;
use App\SharedKernel\Models\Employee;
use Modules\Payroll\Models\PayrollBatch;
use Carbon\Carbon;

/**
 * DeductionService
 *
 * Resolves the full set of deduction line items for a single employee
 * for one payroll cut-off. Returns an ordered array ready to be
 * persisted as PayrollDeduction records.
 *
 * Separation of concerns:
 *   - PayrollComputationService  → orchestrates, persists, computes gross/net
 *   - DeductionService           → resolves WHAT deductions apply and HOW MUCH
 *   - AttendanceService          → resolves attendance-based deductions (LWOP, tardiness)
 */
class DeductionService
{
    /**
     * Fixed working-day denominator per DOLE RO9 payroll rules.
     */
    const DENOMINATOR = 22;

    /**
     * Resolve all deduction line items for one employee in one payroll batch.
     *
     * Returns an array of deduction lines in display_order, each shaped:
     *   [
     *     'deduction_type_id' => int,
     *     'code'              => string,
     *     'name'              => string,
     *     'amount'            => float,   // per cut-off (semi-monthly) amount
     *   ]
     *
     * Only lines with amount > 0 are included.
     *
     * @param  Employee     $employee
     * @param  PayrollBatch $batch
     * @param  float        $ytdGross   Year-to-date gross before this cut-off (for WHT)
     * @return array
     */
    public function resolveDeductions(Employee $employee, PayrollBatch $batch, float $ytdGross = 0.0): array
    {
        $basicMonthly = (float) $employee->basic_monthly_salary;   // alias → basic_salary
        $peraMonthly  = (float) $employee->pera_amount;            // alias → pera
        $payrollDate  = Carbon::create($batch->period_year, $batch->period_month, 1)->toDateString();

        // ── Load all active deduction types (ordered for payslip display) ──
        $allTypes = DeductionType::active()->ordered()->get();

        // ── Load employee enrollments active on this payroll date ──────────
        $enrollments = $employee->deductionEnrollments()
            ->with('deductionType')
            ->activeOn($payrollDate)
            ->get()
            ->keyBy(fn ($e) => $e->deductionType->code);

        // ── Pre-compute the four mandatory government deductions ────────────
        $computed = [
            'PAG_IBIG_1'           => $this->computePagibig1($basicMonthly),
            'PHILHEALTH'           => $this->computePhilHealth($basicMonthly),
            'GSIS_LIFE_RETIREMENT' => $this->computeGsisLife($basicMonthly),
            'WITHHOLDING_TAX'      => $this->computeWithholdingTax(
                                          $employee, $basicMonthly, $peraMonthly,
                                          $ytdGross, $batch
                                      ),
        ];

        $lines = [];

        foreach ($allTypes as $type) {
            $amount = 0.00;

            if ($type->is_computed) {
                // Government-mandated: computed from salary
                $amount = $computed[$type->code] ?? 0.00;
            } elseif (isset($enrollments[$type->code])) {
                // Fixed / loan: enrolled amount (already semi-monthly in DB)
                $amount = (float) $enrollments[$type->code]->amount;
            }

            if ($amount > 0.00) {
                $lines[] = [
                    'deduction_type_id' => $type->id,
                    'code'              => $type->code,
                    'name'              => $type->name,
                    'amount'            => round($amount, 2),
                ];
            }
        }

        return $lines;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Computed Government-Mandatory Deduction Helpers
    //  All return the PER CUT-OFF (semi-monthly) amount.
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Pag-IBIG I (HDMF) — Employee Share
     *
     * Rate:
     *   Basic ≤ ₱1,500  →  1%
     *   Basic  > ₱1,500  →  2%
     *   Monthly EE cap: ₱100
     *
     * Returns: monthly EE ÷ 2  (per cut-off)
     */
    public function computePagibig1(float $basicMonthly): float
    {
        $rate      = $basicMonthly <= 1_500.00 ? 0.01 : 0.02;
        $monthlyEE = min(round($basicMonthly * $rate, 2), 100.00);

        return round($monthlyEE / 2, 2);
    }

    /**
     * PhilHealth — Employee Share (2024 rate: 5% of basic monthly salary)
     *
     * Monthly premium = 5% of basic
     *   Floor: ₱500/month
     *   Ceiling: ₱5,000/month
     * EE Share = 50% of monthly premium
     *
     * Returns: monthly EE share ÷ 2  (per cut-off)
     *
     * NOTE: DOLE RO9 uses the basic monthly salary (not the annual basis)
     * to compute the monthly premium directly.
     */
    public function computePhilHealth(float $basicMonthly): float
    {
        $monthlyPremium = max(500.00, min(round($basicMonthly * 0.05, 2), 5_000.00));
        $monthlyEE      = round($monthlyPremium / 2, 2);   // 50% EE share

        return round($monthlyEE / 2, 2);                   // per cut-off
    }

    /**
     * GSIS Life & Retirement — Personal Share (PS)
     *
     * Rate: 9% of basic monthly salary
     *
     * Returns: monthly PS ÷ 2  (per cut-off)
     */
    public function computeGsisLife(float $basicMonthly): float
    {
        $monthlyPS = round($basicMonthly * 0.09, 2);

        return round($monthlyPS / 2, 2);
    }

    /**
     * Withholding Tax — Annualized Method (BIR TRAIN Law, 2023+)
     *
     * Algorithm:
     *   1. Determine which cut-off number we are on (1–24 in the calendar year).
     *   2. Accumulate gross = ytdGross + this cut-off's gross.
     *   3. Project annual taxable = (accumulated ÷ cut-off number) × 24.
     *   4. Subtract annual non-taxable: GSIS PS + PhilHealth EE + Pag-IBIG EE.
     *   5. Apply BIR graduated table → annual tax.
     *   6. Per cut-off WHT = annual tax ÷ 24.
     *
     * ⚠ STUB NOTE: ytdGross is currently passed as 0 from PayrollComputationService
     * because attendance / YTD tracking is not yet wired. This will be refined
     * in Phase 3A when YTD accumulation logic is implemented.
     *
     * Returns: per cut-off WHT amount (never negative)
     */
    public function computeWithholdingTax(
        Employee     $employee,
        float        $basicMonthly,
        float        $peraMonthly,
        float        $ytdGross,
        PayrollBatch $batch
    ): float {
        // Which cut-off number in the year (1 = Jan 1st, 24 = Dec 2nd)?
        $cutoffNumber = ($batch->period_month - 1) * 2 + ($batch->cutoff === '1st' ? 1 : 2);
        $cutoffNumber = max(1, $cutoffNumber);

        // This cut-off's gross contribution
        $thisGross        = round(($basicMonthly + $peraMonthly) / 2, 2);
        $accumulatedGross = $ytdGross + $thisGross;

        // Project to full year
        $projectedAnnual = round($accumulatedGross / $cutoffNumber * 24, 2);

        // Annual non-taxable deductions
        $annualGSIS = round($basicMonthly * 0.09 * 12, 2);
        $annualPHIC = max(6_000.00, min(round($basicMonthly * 0.05 * 12, 2), 60_000.00));
        $annualHDMF = min(round($basicMonthly * 0.02 * 12, 2), 1_200.00);

        $taxableIncome = max(0.00, $projectedAnnual - $annualGSIS - $annualPHIC - $annualHDMF);
        $annualTax     = $this->birGraduatedTax($taxableIncome);

        return max(0.00, round($annualTax / 24, 2));
    }

    /**
     * BIR Graduated Income Tax — TRAIN Law (effective 2023+)
     *
     *   ≤ 250,000             →  0%
     *   250,001 – 400,000     →  15% of excess over 250,000
     *   400,001 – 800,000     →  22,500 + 20% of excess over 400,000
     *   800,001 – 2,000,000   →  102,500 + 25% of excess over 800,000
     *   2,000,001 – 8,000,000 →  402,500 + 30% of excess over 2,000,000
     *   > 8,000,000           →  2,202,500 + 35% of excess over 8,000,000
     */
    public function birGraduatedTax(float $taxableIncome): float
    {
        return match (true) {
            $taxableIncome <= 250_000   => 0.00,
            $taxableIncome <= 400_000   => round(($taxableIncome - 250_000) * 0.15, 2),
            $taxableIncome <= 800_000   => round(22_500 + ($taxableIncome - 400_000) * 0.20, 2),
            $taxableIncome <= 2_000_000 => round(102_500 + ($taxableIncome - 800_000) * 0.25, 2),
            $taxableIncome <= 8_000_000 => round(402_500 + ($taxableIncome - 2_000_000) * 0.30, 2),
            default                     => round(2_202_500 + ($taxableIncome - 8_000_000) * 0.35, 2),
        };
    }
}

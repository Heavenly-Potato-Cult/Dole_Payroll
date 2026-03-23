<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\PayrollEntry;
use App\Models\PayrollDeduction;
use App\Models\DeductionType;
use App\Traits\TableIVConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollComputationService
{
    use TableIVConverter;

    /**
     * Fixed working-day denominator per DOLE RO9 payroll rules.
     */
    const DENOMINATOR = 22;

    /**
     * Compute a full payroll entry for one employee in a batch.
     * Persists PayrollEntry + PayrollDeduction rows (upsert-style).
     *
     * @param  Employee      $employee
     * @param  PayrollBatch  $batch
     * @param  array         $attendance  Shape:
     *   [
     *     'lwop_days'       => float,   // leave-without-pay days already approved
     *     'late_minutes'    => int,     // cumulative minutes late for the cut-off
     *     'undertime_mins'  => int,     // cumulative undertime minutes
     *     'ytd_gross'       => float,   // year-to-date gross BEFORE this payroll (for WHT)
     *   ]
     * @return PayrollEntry  (loaded with deductions relation)
     */
    public function computeEntry(Employee $employee, PayrollBatch $batch, array $attendance = []): PayrollEntry
    {
        // ── 1. Attendance defaults ────────────────────────────────────────
        $lwopDays      = (float) ($attendance['lwop_days']      ?? 0);
        $lateMinutes   = (int)   ($attendance['late_minutes']   ?? 0);
        $undertimeMins = (int)   ($attendance['undertime_mins'] ?? 0);
        $ytdGross      = (float) ($attendance['ytd_gross']      ?? 0);

        // ── 2. Gross income components ────────────────────────────────────
        $basicMonthly = (float) $employee->basic_monthly_salary;
        $peraMonthly  = (float) $employee->pera_amount;      // ₱2,000 for most
        $rataMonthly  = (float) ($employee->rata ?? 0);

        $salaryEarned = round($basicMonthly / 2, 2);
        $peraEarned   = round($peraMonthly  / 2, 2);
        $rataEarned   = round($rataMonthly  / 2, 2);
        $grossEarned  = $salaryEarned + $peraEarned + $rataEarned;

        // ── 3. Attendance deductions ──────────────────────────────────────
        //   Daily rate  = basic_monthly / 22
        //   Hourly rate = daily_rate / 8
        //
        //   LWOP   = (lwop_days / 22) * basic_monthly   [full-day absences]
        //   Late   = hours_late * hourly_rate + Table-IV(remaining_mins) * daily_rate
        //   Undertime follows the same rule as late minutes
        //
        //   Per DOLE RO9 rules: deductions hit LEAVE CREDITS first.
        //   The caller / AttendanceService resolves leave credits before
        //   passing lwop_days here — only credit-exhausted days reach this service.

        $dailyRate  = round($basicMonthly / self::DENOMINATOR, 6);
        $hourlyRate = round($dailyRate / 8, 6);

        // LWOP deduction
        $lwopDeduction = round(($lwopDays / self::DENOMINATOR) * $basicMonthly, 2);

        // Tardiness — separate hour and minute components
        $lateHours   = intdiv($lateMinutes, 60);
        $lateRemMins = $lateMinutes % 60;
        $tardiness   = round(
            ($lateHours * $hourlyRate)
            + ($this->minuteEquivalent($lateRemMins) * $dailyRate),
            2
        );

        // Undertime — same conversion as tardiness
        $utHours    = intdiv($undertimeMins, 60);
        $utRemMins  = $undertimeMins % 60;
        $undertimeDed = round(
            ($utHours * $hourlyRate)
            + ($this->minuteEquivalent($utRemMins) * $dailyRate),
            2
        );

        $totalAttendanceDed = round($lwopDeduction + $tardiness + $undertimeDed, 2);

        // ── 4. Build deduction lines ──────────────────────────────────────
        $payrollDate    = Carbon::create($batch->period_year, $batch->period_month, 1);
        $deductionTypes = DeductionType::orderBy('display_order')->get()->keyBy('code');
        $deductionLines = [];   // [ ['type_id'=>, 'code'=>, 'name'=>, 'amount'=>] ]

// TO — pass as string to match your model's scope signature
$enrollments = $employee->deductionEnrollments()
    ->with('deductionType')
    ->activeOn($payrollDate->toDateString())
    ->get()
    ->keyBy(fn ($e) => $e->deductionType->code);

        // ── 4a. Computed government-mandatory deductions ──────────────────
        $pagibig1Amount = $this->computePagibig1($basicMonthly);
        $philhealthAmt  = $this->computePhilHealth($basicMonthly);
        $gsisLifeAmt    = $this->computeGsisLife($basicMonthly);
        $whtAmount      = $this->computeWithholdingTax($employee, $basicMonthly, $ytdGross, $batch);

        // ── 4b. Walk ALL deduction types in display_order ─────────────────
        foreach ($deductionTypes as $code => $type) {

            $amount = 0.00;

            if ($type->is_computed) {
                $amount = match ($code) {
                    'PAG_IBIG_1'           => $pagibig1Amount,
                    'PHILHEALTH'           => $philhealthAmt,
                    'GSIS_LIFE_RETIREMENT' => $gsisLifeAmt,
                    'WITHHOLDING_TAX'      => $whtAmount,
                    default                => 0.00,
                };
            } elseif (isset($enrollments[$code])) {
                $amount = (float) $enrollments[$code]->amount;
            }

            if ($amount > 0) {
                $deductionLines[] = [
                    'deduction_type_id' => $type->id,
                    'code'              => $type->code,
                    'name'              => $type->name,
                    'amount'            => round($amount, 2),
                ];
            }
        }

        $totalDeductions = round(
            collect($deductionLines)->sum('amount') + $totalAttendanceDed,
            2
        );

        // ── 5. Net pay ────────────────────────────────────────────────────
        $netAmount = round($grossEarned - $totalDeductions, 2);

        // ── 6. Persist ────────────────────────────────────────────────────
        return DB::transaction(function () use (
            $employee, $batch,
            $salaryEarned, $peraEarned, $rataEarned,
            $lwopDeduction, $tardiness, $undertimeDed,
            $totalDeductions, $netAmount,
            $deductionLines
        ) {
            /** @var PayrollEntry $entry */
            $entry = PayrollEntry::updateOrCreate(
                [
                    'payroll_batch_id' => $batch->id,
                    'employee_id'      => $employee->id,
                ],
                [
                    'basic_salary'     => $salaryEarned,
                    'pera'             => $peraEarned,
                    'rata'             => $rataEarned,
                    'gross_income'     => round($salaryEarned + $peraEarned + $rataEarned, 2),
                    'lwop_deduction'   => $lwopDeduction,
                    'tardiness'        => $tardiness,
                    'undertime'        => $undertimeDed,
                    'total_deductions' => $totalDeductions,
                    'net_amount'       => $netAmount,
                ]
            );

            // Replace deduction lines fresh on every compute
            $entry->deductions()->delete();

            foreach ($deductionLines as $line) {
                PayrollDeduction::create([
                    'payroll_entry_id'  => $entry->id,
                    'deduction_type_id' => $line['deduction_type_id'],
                    'code'              => $line['code'],
                    'name'              => $line['name'],
                    'amount'            => $line['amount'],
                ]);
            }

            return $entry->load('deductions');
        });
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Mandatory / Computed Deduction Helpers
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Pag-IBIG I (HDMF): EE share = 2% of monthly basic.
     * Cap: if basic ≤ ₱1,500 → 1%; above → 2%; max monthly EE = ₱100.
     * Returns the PER CUT-OFF amount (monthly ÷ 2).
     */
    protected function computePagibig1(float $basicMonthly): float
    {
        $rate      = $basicMonthly <= 1500 ? 0.01 : 0.02;
        $monthlyEE = min(round($basicMonthly * $rate, 2), 100.00);
        return round($monthlyEE / 2, 2);
    }

    /**
     * PhilHealth: 5% of basic monthly salary (2024 rate), EE share = 50%.
     * Monthly premium floor = ₱500, ceiling = ₱5,000.
     * Returns per cut-off (monthly EE share ÷ 2).
     */
    protected function computePhilHealth(float $basicMonthly): float
    {
        $monthlyPremium = max(500, min(round($basicMonthly * 12 * 0.05 / 12, 2), 5000));
        $eeShare        = round($monthlyPremium / 2, 2);   // 50% EE share
        return round($eeShare / 2, 2);                     // per cut-off
    }

    /**
     * GSIS Life & Retirement Personal Share (PS) = 9% of basic monthly.
     * Returns per cut-off (monthly ÷ 2).
     */
    protected function computeGsisLife(float $basicMonthly): float
    {
        return round(round($basicMonthly * 0.09, 2) / 2, 2);
    }

    /**
     * Withholding Tax — ANNUALIZED method (January–December).
     *
     * Steps:
     *   1. Project annual taxable income from accumulated YTD gross.
     *   2. Subtract non-taxable: GSIS PS + PhilHealth EE + Pag-IBIG EE (annual).
     *   3. Apply BIR TRAIN Law graduated table.
     *   4. Distribute equally: annualTax ÷ 24 cut-offs.
     */
    protected function computeWithholdingTax(
        Employee     $employee,
        float        $basicMonthly,
        float        $ytdGross,
        PayrollBatch $batch
    ): float {
        // Which cut-off number are we on? (1 = Jan 1st, 24 = Dec 2nd)
        $cutoffNumber = ($batch->period_month - 1) * 2 + ($batch->cutoff === '1st' ? 1 : 2);
        if ($cutoffNumber < 1) $cutoffNumber = 1;

        $peraMonthly = (float) $employee->pera_amount;
        $thisGross   = round(($basicMonthly + $peraMonthly) / 2, 2);

        $accumulatedGross = $ytdGross + $thisGross;
        $projectedAnnual  = round($accumulatedGross / $cutoffNumber * 24, 2);

        // Annual non-taxable deductions (GSIS + PhilHealth + Pag-IBIG)
        $annualGSIS = round($basicMonthly * 0.09 * 12, 2);
        $annualPHIC = max(6000, min(round($basicMonthly * 12 * 0.05, 2), 60000));
        $annualHDMF = min(round($basicMonthly * 0.02 * 12, 2), 1200.00);

        $taxableIncome = max(0, $projectedAnnual - $annualGSIS - $annualPHIC - $annualHDMF);
        $annualTax     = $this->birGraduatedTax($taxableIncome);

        return max(0, round($annualTax / 24, 2));
    }

    /**
     * BIR Graduated Income Tax — TRAIN Law (effective 2023+).
     *
     *   ≤ 250,000             →  0%
     *   250,001 – 400,000     →  15% of excess over 250,000
     *   400,001 – 800,000     →  22,500 + 20% of excess over 400,000
     *   800,001 – 2,000,000   →  102,500 + 25% of excess over 800,000
     *   2,000,001 – 8,000,000 →  402,500 + 30% of excess over 2,000,000
     *   > 8,000,000           →  2,202,500 + 35% of excess over 8,000,000
     */
    protected function birGraduatedTax(float $taxableIncome): float
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

    // ═══════════════════════════════════════════════════════════════════
    //  Table IV helper
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Wraps minutesToDays() from the TableIVConverter trait.
     * This is the same method AttendanceService uses — consistent across the app.
     *
     * Example: 15 minutes → 0.031 (Table IV lookup, NOT 0.03125 computed)
     */
    protected function minuteEquivalent(int $minutes): float
    {
        if ($minutes <= 0) return 0.0;

        // minutesToDays() is defined in App\Traits\TableIVConverter
        return (float) $this->minutesToDays($minutes);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Batch-level entry point
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Run computeEntry() for every active employee in one go.
     * Called by PayrollController@compute.
     *
     * @param  PayrollBatch $batch
     * @param  array        $attendanceMap  [ employee_id => attendance array ]
     * @return array  ['computed' => int, 'errors' => string[]]
     */
    public function computeBatch(PayrollBatch $batch, array $attendanceMap = []): array
    {
        $employees = \App\Models\Employee::where('status', 'active')
            ->with(['deductionEnrollments.deductionType'])
            ->get();

        $computed = 0;
        $errors   = [];

        foreach ($employees as $employee) {
            try {
                $attendance = $attendanceMap[$employee->id] ?? [];
                $this->computeEntry($employee, $batch, $attendance);
                $computed++;
            } catch (\Throwable $e) {
                Log::error("Payroll compute error — Employee #{$employee->id}: " . $e->getMessage());
                $errors[] = "#{$employee->id} {$employee->full_name}: " . $e->getMessage();
            }
        }

        return compact('computed', 'errors');
    }
}
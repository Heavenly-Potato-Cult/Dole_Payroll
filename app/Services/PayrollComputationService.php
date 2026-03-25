<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\PayrollEntry;
use App\Models\PayrollDeduction;
use App\Traits\TableIVConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollComputationService
{
    use TableIVConverter;

    /**
     * Fixed working-day denominator per DOLE RO9 payroll rules.
     */
    const DENOMINATOR = 22;

    /**
     * Injected via constructor so it can be swapped/mocked in tests.
     */
    protected DeductionService $deductionService;

    public function __construct(DeductionService $deductionService)
    {
        $this->deductionService = $deductionService;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Public API
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Compute a full payroll entry for one employee in a batch.
     * Persists PayrollEntry + PayrollDeduction rows (upsert-style).
     *
     * @param  Employee      $employee
     * @param  PayrollBatch  $batch
     * @param  array         $attendance  Shape:
     *   [
     *     'lwop_days'       => float,   // leave-without-pay days (credit-exhausted only)
     *     'late_minutes'    => int,     // cumulative minutes late for the cut-off
     *     'undertime_mins'  => int,     // cumulative undertime minutes
     *     'ytd_gross'       => float,   // YTD gross BEFORE this cut-off (for WHT)
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

        // ── 2. Gross income components (semi-monthly = monthly ÷ 2) ──────
        $basicMonthly = (float) $employee->basic_monthly_salary;   // alias → basic_salary
        $peraMonthly  = (float) $employee->pera_amount;            // alias → pera
        $rataMonthly  = (float) ($employee->rata ?? 0);

        $salaryEarned = round($basicMonthly / 2, 2);
        $peraEarned   = round($peraMonthly  / 2, 2);
        $rataEarned   = round($rataMonthly  / 2, 2);
        $grossEarned  = round($salaryEarned + $peraEarned + $rataEarned, 2);

        // ── 3. Attendance deductions ──────────────────────────────────────
        //
        //   Daily rate  = basic_monthly / 22
        //   Hourly rate = daily_rate    / 8
        //
        //   LWOP   = (lwop_days / 22) × basic_monthly
        //   Late   = hours_late × hourly_rate + Table-IV(remaining_mins) × daily_rate
        //   Undertime follows the same rule as late minutes
        //
        //   Per DOLE RO9 rules: attendance deductions hit LEAVE CREDITS first.
        //   AttendanceService resolves leave credits before passing lwop_days here —
        //   only credit-exhausted absences reach this service.

        $dailyRate  = round($basicMonthly / self::DENOMINATOR, 6);
        $hourlyRate = round($dailyRate / 8, 6);

        // LWOP
        $lwopDeduction = round(($lwopDays / self::DENOMINATOR) * $basicMonthly, 2);

        // Tardiness (late)
        $lateHours      = intdiv($lateMinutes, 60);
        $lateRemMins    = $lateMinutes % 60;
        $tardiness      = round(
            ($lateHours * $hourlyRate)
            + ($this->minuteEquivalent($lateRemMins) * $dailyRate),
            2
        );

        // Undertime
        $utHours      = intdiv($undertimeMins, 60);
        $utRemMins    = $undertimeMins % 60;
        $undertimeDed = round(
            ($utHours * $hourlyRate)
            + ($this->minuteEquivalent($utRemMins) * $dailyRate),
            2
        );

        $totalAttendanceDed = round($lwopDeduction + $tardiness + $undertimeDed, 2);

        // ── 4. Resolve deduction lines via DeductionService ───────────────
        //
        //   DeductionService returns an ordered array of lines, each:
        //   [ 'deduction_type_id', 'code', 'name', 'amount' ]
        //
        //   Only lines with amount > 0 are included.
        //   The order matches display_order on deduction_types (payslip order).

        $deductionLines = $this->deductionService->resolveDeductions(
            $employee,
            $batch,
            $ytdGross
        );

        // ── 5. Net pay ────────────────────────────────────────────────────
        $totalDedLines   = round(collect($deductionLines)->sum('amount'), 2);
        $totalDeductions = round($totalDedLines + $totalAttendanceDed, 2);
        $netAmount       = round($grossEarned - $totalDeductions, 2);

        // ── 6. Persist ────────────────────────────────────────────────────
        return DB::transaction(function () use (
            $employee, $batch,
            $salaryEarned, $peraEarned, $rataEarned, $grossEarned,
            $lwopDays, $lwopDeduction, $tardiness, $undertimeDed,
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
                    'gross_income'     => $grossEarned,
                    'lwop_days'        => 0,               // set from AttendanceService when wired
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
        $employees = Employee::where('status', 'active')
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

    // ═══════════════════════════════════════════════════════════════════
    //  Private helpers
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Convert minutes (the remaining after stripping whole hours) to
     * Table IV decimal day equivalent via the TableIVConverter trait.
     *
     * Example: 15 minutes → 0.031  (Table IV lookup, NOT 0.03125 computed)
     */
    private function minuteEquivalent(int $minutes): float
    {
        if ($minutes <= 0) return 0.0;

        // minutesToDays() is defined in App\Traits\TableIVConverter
        return (float) $this->minutesToDays($minutes);
    }
}
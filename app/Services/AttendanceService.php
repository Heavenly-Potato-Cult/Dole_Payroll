<?php

namespace App\Services;

use App\Models\Employee;
use App\Traits\TableIVConverter;

class AttendanceService
{
    use TableIVConverter;

    /**
     * Fixed working-day denominator per DOLE RO9 payroll rules.
     * All salary computations use 22 regardless of actual calendar days.
     */
    const WORK_DAYS_DENOMINATOR = 22;

    /**
     * Process HRIS attendance data and compute all deduction amounts
     * for a single employee for one cut-off period.
     *
     * @param  Employee $employee
     * @param  array    $attendance  Output of HrisApiService::fetchAttendance()
     *
     * @return array {
     *   lwop_salary:       float,  // LWOP deduction from basic salary
     *   lwop_pera:         float,  // LWOP deduction from PERA
     *   tardiness_amount:  float,  // Late + undertime deduction
     *   lwop_days:         float,  // From HRIS (for payslip display)
     *   late_minutes:      int,    // From HRIS (for payslip display)
     *   undertime_minutes: int,    // From HRIS (for payslip display)
     *   tardiness_days:    float,  // Table IV equivalent days for tardiness
     *   total_deduction:   float,  // lwop_salary + lwop_pera + tardiness_amount
     * }
     */
    public function compute(Employee $employee, array $attendance): array
    {
        $basicSalary  = (float) $employee->basic_salary;
        $pera         = (float) $employee->pera;
        $denom        = self::WORK_DAYS_DENOMINATOR;

        $lwopDays         = (float) ($attendance['lwop_days']         ?? 0);
        $lateMinutes      = (int)   ($attendance['late_minutes']      ?? 0);
        $undertimeMinutes = (int)   ($attendance['undertime_minutes'] ?? 0);

        // ── 1. LWOP deductions ────────────────────────────────────
        // Formula: (lwop_days / 22) × monthly_amount
        $lwopSalary = round(($lwopDays / $denom) * $basicSalary, 2);
        $lwopPera   = round(($lwopDays / $denom) * $pera, 2);

        // ── 2. Tardiness deduction (late + undertime) ─────────────
        // Convert total tardy minutes to Table IV decimal day equivalent
        // then multiply by daily rate.
        $totalTardyMinutes = $lateMinutes + $undertimeMinutes;
        $tardinessDays     = $this->minutesToDays($totalTardyMinutes);
        $dailyRate         = round($basicSalary / $denom, 4);
        $tardinessAmount   = round($tardinessDays * $dailyRate, 2);

        return [
            'lwop_salary'       => $lwopSalary,
            'lwop_pera'         => $lwopPera,
            'tardiness_amount'  => $tardinessAmount,
            'lwop_days'         => $lwopDays,
            'late_minutes'      => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'tardiness_days'    => $tardinessDays,
            'total_deduction'   => round($lwopSalary + $lwopPera + $tardinessAmount, 2),
        ];
    }

    /**
     * Convenience: compute for a semi-monthly cut-off where basic_salary
     * is already the semi-monthly amount (i.e. monthly ÷ 2).
     *
     * Use this when the payroll entry is already working with half-month figures.
     */
    public function computeSemiMonthly(Employee $employee, array $attendance): array
    {
        // Clone employee with halved salary for the computation
        $semiEmployee               = clone $employee;
        $semiEmployee->basic_salary = round($employee->basic_salary / 2, 2);
        $semiEmployee->pera         = round($employee->pera / 2, 2);

        return $this->compute($semiEmployee, $attendance);
    }
}
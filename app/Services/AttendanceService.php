<?php

namespace App\Services;

use App\Models\AttendanceSnapshot;
use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Traits\TableIVConverter;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    use TableIVConverter;

    /**
     * Fixed working-day denominator per DOLE RO9 payroll rules.
     */
    const WORK_DAYS_DENOMINATOR = 22;

    // ═══════════════════════════════════════════════════════════════════
    //  STEP 1 — Pull from HRIS and store (called from PayrollController)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Fetch attendance for ALL active employees for a batch's cut-off period
     * and upsert into attendance_snapshots.
     *
     * Re-pulling is safe — upsert overwrites existing rows for the same
     * (payroll_batch_id, employee_id) pair. Any HR corrections are reset
     * on a re-pull, which is intentional so stale corrections don't persist.
     *
     * @param  PayrollBatch $batch
     * @return array  ['pulled' => int, 'errors' => string[]]
     */
    public function pullForBatch(PayrollBatch $batch): array
    {
        $hris = app(HrisApiService::class);

        // ONE bulk HTTP call instead of 82
        $attendanceMap = $hris->fetchAttendanceBulk(
            $batch->period_start,
            $batch->period_end
        );

        if (empty($attendanceMap)) {
            return ['pulled' => 0, 'errors' => ['Bulk attendance fetch returned no data.']];
        }

        $employees = Employee::where('status', 'active')->orderBy('id')->get();
        $firstId   = $employees->first()->id; // = 8
        $pulled    = 0;
        $errors    = [];

        foreach ($employees as $employee) {
            try {
                // Map DB id (8–89) to API key (EMP001–EMP082)
                $apiKey = 'EMP' . str_pad($employee->id - $firstId + 1, 3, '0', STR_PAD_LEFT);

                $raw = $attendanceMap[$apiKey] ?? null;

                if (! $raw) {
                    $errors[] = "#{$employee->id} {$employee->full_name}: not found in API (key: {$apiKey})";
                    continue;
                }

                AttendanceSnapshot::updateOrCreate(
                    [
                        'payroll_batch_id' => $batch->id,
                        'employee_id'      => $employee->id,
                    ],
                    [
                        'days_present'      => (float) ($raw['days_present']      ?? 0),
                        'lwop_days'         => (float) ($raw['lwop_days']         ?? 0),
                        'late_minutes'      => (int)   ($raw['late_minutes']      ?? 0),
                        'undertime_minutes' => (int)   ($raw['undertime_minutes'] ?? 0),
                        'is_corrected'      => false,
                        'correction_note'   => null,
                        'corrected_by'      => null,
                        'corrected_at'      => null,
                        'source'            => 'hris_api',
                        'fetched_at'        => now(),
                    ]
                );

                $pulled++;
            } catch (\Throwable $e) {
                Log::error("Attendance snapshot failed — Employee #{$employee->id}: " . $e->getMessage());
                $errors[] = "#{$employee->id} {$employee->full_name}: " . $e->getMessage();
            }
        }

        Log::info("Attendance pull complete for batch #{$batch->id}", [
            'pulled' => $pulled,
            'errors' => count($errors),
        ]);

        return compact('pulled', 'errors');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  STEP 2 — Read snapshots (called from PayrollController@compute)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Return the stored attendance map for a batch, keyed by employee_id (integer).
     * Reads from attendance_snapshots — does NOT call the HRIS API.
     *
     * Returns an empty array if no snapshots exist yet (batch hasn't been pulled).
     * PayrollController@compute checks for this and blocks computation.
     *
     * @param  PayrollBatch $batch
     * @return array  [ employee_id (int) => [ 'lwop_days'=>, 'late_minutes'=>, ... ] ]
     */
    public function getAttendanceForBatch(PayrollBatch $batch): array
    {
        return AttendanceSnapshot::where('payroll_batch_id', $batch->id)
            ->get()
            ->keyBy('employee_id')
            ->map(fn (AttendanceSnapshot $snap) => $snap->toAttendanceArray())
            ->toArray();
    }

    /**
     * How many snapshots exist for this batch.
     * Used by PayrollController to gate the Compute button.
     */
    public function snapshotCount(PayrollBatch $batch): int
    {
        return AttendanceSnapshot::where('payroll_batch_id', $batch->id)->count();
    }

    /**
     * How many snapshots for this batch have been manually corrected by HR.
     * Shown in the show.blade.php attendance review panel.
     */
    public function correctedCount(PayrollBatch $batch): int
    {
        return AttendanceSnapshot::where('payroll_batch_id', $batch->id)
            ->where('is_corrected', true)
            ->count();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  HR Correction
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Allow HR to manually override one employee's attendance snapshot.
     * Called from a future AttendanceController (or inline from PayrollController).
     *
     * @param  AttendanceSnapshot $snapshot
     * @param  array              $data  keys: lwop_days, late_minutes, undertime_minutes, correction_note
     * @param  int                $userId  Auth::id() of the HR officer making the correction
     */
    public function correctSnapshot(AttendanceSnapshot $snapshot, array $data, int $userId): void
    {
        $snapshot->update([
            'lwop_days'         => (float) ($data['lwop_days']         ?? $snapshot->lwop_days),
            'late_minutes'      => (int)   ($data['late_minutes']      ?? $snapshot->late_minutes),
            'undertime_minutes' => (int)   ($data['undertime_minutes'] ?? $snapshot->undertime_minutes),
            'is_corrected'      => true,
            'correction_note'   => $data['correction_note'] ?? null,
            'corrected_by'      => $userId,
            'corrected_at'      => now(),
            'source'            => 'manual',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Single-employee compute (unchanged — used by PayrollComputationService)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Process attendance data and compute deduction amounts for one employee.
     * Input is the raw array (from snapshot or directly), not a model.
     *
     * @return array {
     *   lwop_salary, lwop_pera, tardiness_amount,
     *   lwop_days, late_minutes, undertime_minutes,
     *   tardiness_days, total_deduction
     * }
     */
    public function compute(Employee $employee, array $attendance): array
    {
        $basicSalary      = (float) $employee->basic_salary;
        $pera             = (float) $employee->pera;
        $denom            = self::WORK_DAYS_DENOMINATOR;

        $lwopDays         = (float) ($attendance['lwop_days']         ?? 0);
        $lateMinutes      = (int)   ($attendance['late_minutes']      ?? 0);
        $undertimeMinutes = (int)   ($attendance['undertime_minutes'] ?? 0);

        // LWOP deductions
        $lwopSalary = round(($lwopDays / $denom) * $basicSalary, 2);
        $lwopPera   = round(($lwopDays / $denom) * $pera, 2);

        // Tardiness (late + undertime combined via Table IV)
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
}
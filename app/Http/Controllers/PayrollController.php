<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComputePayrollRequest;
use App\Models\AttendanceSnapshot;
use App\Models\PayrollBatch;
use App\Models\PayrollAuditLog;
use App\Models\Signatory;
use App\Services\AttendanceService;
use App\Services\PayrollComputationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    const STATUS_LABELS = [
        'draft'              => 'Draft',
        'computed'           => 'Computed',
        'pending_accountant' => 'Pending Accountant',
        'pending_rd'         => 'Pending RD/ARD',
        'released'           => 'Released',
        'locked'             => 'Locked',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PayrollBatch::with('creator')
            ->withCount('entries')
            ->withSum('entries', 'gross_income')
            ->withSum('entries', 'total_deductions')
            ->withSum('entries', 'net_amount')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id');

        // Employees can only see released/locked batches
        if (!\App\Services\RoleService::canAccessPayroll($user)) {
            $query->whereIn('status', ['released', 'locked']);
        }

        if ($request->filled('year'))   $query->where('period_year',  $request->year);
        if ($request->filled('month'))  $query->where('period_month', $request->month);
        if ($request->filled('status')) $query->where('status',       $request->status);

        $batches = $query->paginate(15)->withQueryString();

        return view('payroll.index', compact('batches'));
    }

    public function myPayslip(Request $request)
    {
        $user = Auth::user();
        $employeeId = session('hris_employee_id');
        
        // Get employee's payroll entries from released/locked batches only
        $query = \App\Models\PayrollEntry::with(['batch', 'employee'])
            ->whereHas('batch', function ($q) {
                $q->whereIn('status', ['released', 'locked']);
            });

        // Filter by employee ID (for HRIS users)
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        } elseif ($user->employee) {
            $query->where('employee_id', $user->employee->id);
        } else {
            // If no employee association, return empty
            $entries = collect([]);
            return view('payroll.my-payslip', compact('entries'));
        }

        $entries = $query->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('payroll.my-payslip', compact('entries'));
    }

    public function create()
    {
        $this->authorizeRole(\App\Services\RoleService::getRoleGroup('payroll_create'));

        $currentYear  = now()->year;
        $currentMonth = now()->month;
        $years        = range($currentYear - 2, $currentYear + 1);

        return view('payroll.create', compact('currentYear', 'currentMonth', 'years'));
    }

    public function store(ComputePayrollRequest $request)
    {
        $year   = (int) $request->period_year;
        $month  = (int) $request->period_month;
        $cutoff = $request->cutoff;

        $exists = PayrollBatch::where([
            'period_year'  => $year,
            'period_month' => $month,
            'cutoff'       => $cutoff,
        ])->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', "A {$cutoff} cut-off payroll batch for {$request->periodLabel()} already exists.");
        }

        $periodStart = $cutoff === '1st'
            ? \Carbon\Carbon::create($year, $month, 1)
            : \Carbon\Carbon::create($year, $month, 16);

        $periodEnd = $cutoff === '1st'
            ? \Carbon\Carbon::create($year, $month, 15)
            : \Carbon\Carbon::create($year, $month)->endOfMonth();

        $batch = PayrollBatch::create([
            'period_year'  => $year,
            'period_month' => $month,
            'cutoff'       => $cutoff,
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
            'status'       => 'draft',
            'created_by'   => Auth::id(),
        ]);

        $this->log($batch, 'created', null, 'draft');

        return redirect()->route('payroll.show', $batch)
            ->with('success', "Payroll batch created for {$request->periodLabel()}. Pull attendance first, then click Compute.");
    }

    public function show(PayrollBatch $payroll)
    {
        $payroll->load(['entries.employee', 'entries.deductions.deductionType', 'creator', 'auditLogs.user']);

        $entries       = $payroll->entries->sortBy(fn ($e) => optional($e->employee)->last_name ?? '');
        $totalGross    = $payroll->entries->sum('gross_income');
        $totalDeds     = $payroll->entries->sum('total_deductions');
        $totalNet      = $payroll->entries->sum('net_amount');
        $employeeCount = $payroll->entries->count();
        $auditLogs     = $payroll->auditLogs->sortByDesc('performed_at');

        // ── Attendance snapshot summary for the action panel ────────────
        $attendanceService = app(AttendanceService::class);
        $snapshotCount     = $attendanceService->snapshotCount($payroll);
        $correctedCount    = $attendanceService->correctedCount($payroll);
        $activeCount       = \App\Models\Employee::where('status', 'active')->count();

        // Pass snapshots for the review table (only on draft/computed where HR still acts)
        $snapshots = in_array($payroll->status, ['draft', 'computed'])
            ? AttendanceSnapshot::where('payroll_batch_id', $payroll->id)
                ->with('employee:id,last_name,first_name,employee_no')
                ->orderBy('employee_id')
                ->get()
            : collect();

        return view('payroll.show', compact(
            'payroll', 'entries',
            'totalGross', 'totalDeds', 'totalNet', 'employeeCount',
            'auditLogs',
            'snapshotCount', 'correctedCount', 'activeCount', 'snapshots'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Pull Attendance from HRIS API and store snapshots
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Pull attendance from the HRIS API for all active employees
     * and store into attendance_snapshots.
     *
     * Safe to re-run — existing snapshots are overwritten.
     * Any HR corrections made previously will be reset on re-pull.
     */
    public function pullAttendance(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('compute', $payroll);

        // Only allow pulling on batches that haven't been fully approved yet
        if (in_array($payroll->status, ['released', 'locked'])) {
            return back()->with('error', 'Cannot re-pull attendance for a released or locked batch.');
        }

        $result = app(AttendanceService::class)->pullForBatch($payroll);

        $message = "Attendance pulled: {$result['pulled']} employee(s) recorded.";

        if (! empty($result['errors'])) {
            return redirect()->route('payroll.show', $payroll)
                ->with('warning', "{$message} Some employees failed: " . implode('; ', $result['errors']));
        }

        $this->log($payroll, 'attendance_pulled', null, "pulled:{$result['pulled']}");

        return redirect()->route('payroll.show', $payroll)
            ->with('success', "{$message} Review the attendance records below, then click Compute.");
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Compute — reads from snapshots, not live API
    // ═══════════════════════════════════════════════════════════════════

    public function compute(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('compute', $payroll);

        $attendanceService = app(AttendanceService::class);

        // Guard: block computation if attendance hasn't been pulled yet
        if ($attendanceService->snapshotCount($payroll) === 0) {
            return redirect()->route('payroll.show', $payroll)
                ->with('error', 'Attendance has not been pulled yet. Click "Pull Attendance" first.');
        }

        // Read from stored snapshots — NO live API call here
        $attendanceMap = $attendanceService->getAttendanceForBatch($payroll);

        $result = app(PayrollComputationService::class)->computeBatch($payroll, $attendanceMap);

        if ($payroll->status === 'draft') {
            $payroll->update(['status' => 'computed']);
            $this->log($payroll, 'computed', 'draft', 'computed');
        }

        $message = "Computation complete: {$result['computed']} employee(s) processed.";

        if (! empty($result['errors'])) {
            return redirect()->route('payroll.show', $payroll)
                ->with('warning', "{$message} Errors: " . implode('; ', $result['errors']));
        }

        return redirect()->route('payroll.show', $payroll)->with('success', $message);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Approval pipeline
    // ═══════════════════════════════════════════════════════════════════

    public function submit(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('submit', $payroll);
        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $old = $payroll->status;
        $payroll->update([
            'status'      => 'pending_accountant',
            'prepared_at' => now(),
            'remarks'     => $request->input('remarks'),
        ]);
        $this->log($payroll, 'Submitted for Accountant Review', $old, 'pending_accountant');

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll batch submitted to the Accountant for review.');
    }

    public function certify(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('certify', $payroll);
        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $old = $payroll->status;
        $payroll->update([
            'status'      => 'pending_rd',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'remarks'     => $request->input('remarks') ?? $payroll->remarks,
        ]);
        $this->log($payroll, 'Funds Certified — Forwarded to RD/ARD', $old, 'pending_rd');

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll certified. Forwarded to RD/ARD for approval.');
    }

    public function approve(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('approve', $payroll);
        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $old = $payroll->status;
        $payroll->update([
            'status'      => 'released',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'released_at' => now(),
            'remarks'     => $request->input('remarks') ?? $payroll->remarks,
        ]);
        $this->log($payroll, 'Approved & Released by RD/ARD', $old, 'released');

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll approved and released.');
    }

    public function lock(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('lock', $payroll);
        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $old = $payroll->status;
        $payroll->update([
            'status'      => 'locked',
            'released_by' => Auth::id(),
            'remarks'     => $request->input('remarks') ?? $payroll->remarks,
        ]);
        $this->log($payroll, 'Locked after Disbursement', $old, 'locked');

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll batch locked. Disbursement complete.');
    }

    public function destroy(PayrollBatch $payroll)
    {
        $this->authorize('delete', $payroll);

        DB::transaction(function () use ($payroll) {
            foreach ($payroll->entries as $entry) {
                $entry->deductions()->delete();
            }
            $payroll->entries()->delete();
            $payroll->auditLogs()->delete();
            AttendanceSnapshot::where('payroll_batch_id', $payroll->id)->delete();
            $payroll->delete();
        });

        return redirect()->route('payroll.index')
            ->with('success', 'Draft payroll batch deleted.');
    }

    public function verify(PayrollBatch $payroll)
    {
        $this->authorize('view', $payroll);
        $payroll->load(['entries.employee', 'entries.deductions']);

        $siblingCutoff = $payroll->cutoff === '1st' ? '2nd' : '1st';
        $siblingBatch  = PayrollBatch::with(['entries.employee', 'entries.deductions'])
            ->where('period_year',  $payroll->period_year)
            ->where('period_month', $payroll->period_month)
            ->where('cutoff',       $siblingCutoff)
            ->first();

        $siblingEntries = $siblingBatch
            ? $siblingBatch->entries->keyBy('employee_id')
            : collect();

        $verifyRows = $payroll->entries
            ->sortBy(fn ($e) => optional($e->employee)->last_name . optional($e->employee)->first_name)
            ->map(function ($entry) use ($siblingEntries) {
                $sibling    = $siblingEntries->get($entry->employee_id);
                $netCurrent = (float) $entry->net_amount;
                $netSibling = $sibling ? (float) $sibling->net_amount : null;

                $hasLbpLoan = $entry->deductions->contains(
                    fn ($d) => stripos($d->code ?? '', 'lbp') !== false
                            || stripos($d->name ?? '', 'lbp') !== false
                );

                return (object) [
                    'employee'        => $entry->employee,
                    'entry_current'   => $entry,
                    'entry_sibling'   => $sibling,
                    'net_current'     => $netCurrent,
                    'net_sibling'     => $netSibling,
                    'total_net'       => $netCurrent + ($netSibling ?? 0),
                    'has_lbp_loan'    => $hasLbpLoan,
                    'below_threshold' => $netCurrent < 5000 || ($netSibling !== null && $netSibling < 5000),
                ];
            })
            ->values();

        [$totalNet1st, $totalNet2nd] = $payroll->cutoff === '1st'
            ? [$payroll->entries->sum('net_amount'), $siblingBatch?->entries->sum('net_amount') ?? 0]
            : [$siblingBatch?->entries->sum('net_amount') ?? 0, $payroll->entries->sum('net_amount')];

        $totalCombined       = $totalNet1st + $totalNet2nd;
        $belowThresholdCount = $verifyRows->filter(fn ($r) => $r->below_threshold)->count();

        return view('payroll.verify', compact(
            'payroll', 'siblingBatch', 'verifyRows',
            'totalNet1st', 'totalNet2nd', 'totalCombined', 'belowThresholdCount'
        ));
    }

    public function forceEdit(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('forceEdit', $payroll);
        $request->validate(['remarks' => ['required', 'string', 'min:10', 'max:1000']]);

        $old = $payroll->status;
        $payroll->update(['status' => 'released', 'remarks' => $request->input('remarks')]);
        $this->log($payroll, 'Force Edit Override', $old, 'released');

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll batch unlocked. Status reverted to Released for corrections.');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Payslip Generation
    //
    //  GET /payroll/{payroll}/payslips/generate
    //      ?mode      = consolidated (default) | per_batch
    //      ?entry_id  = <PayrollEntry id>  (optional — single employee only)
    //
    //  consolidated  → single payslip per employee showing BOTH cut-offs
    //                  side-by-side. Matches the existing payslip Blade layout.
    //                  Requires the sibling batch to also be released/locked.
    //                  If no sibling exists the 2nd column will render empty.
    //
    //  per_batch     → one payslip per employee for THIS batch's cut-off only.
    //                  The opposite column is left blank.
    // ═══════════════════════════════════════════════════════════════════

    public function generatePayslips(Request $request, PayrollBatch $payroll)
    {
        // ── Guard: only released or locked batches ───────────────────────
        if (! in_array($payroll->status, ['released', 'locked'])) {
            abort(403, 'Payslips are only available after the batch has been released.');
        }

        $mode    = $request->input('mode', 'consolidated');
        $entryId = $request->input('entry_id');

        // ── Resolve sibling batch (other cut-off, same month/year) ───────
        $siblingCutoff = $payroll->cutoff === '1st' ? '2nd' : '1st';
        $sibling = PayrollBatch::where('period_year',  $payroll->period_year)
                               ->where('period_month', $payroll->period_month)
                               ->where('cutoff',       $siblingCutoff)
                               ->first();

        // ── Entries to print ─────────────────────────────────────────────
        $query = $payroll->entries()
                         ->with(['employee.division', 'deductions.deductionType'])
                         ->orderBy(
                             \App\Models\Employee::select('last_name')
                                 ->whereColumn('employees.id', 'payroll_entries.employee_id'),
                             'asc'
                         );

        if ($entryId) {
            $query->where('id', $entryId);
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            abort(404, 'No payroll entries found for the given parameters.');
        }

        // ── Active HRMO Designate signatory ──────────────────────────────
        // Falls back to a safe placeholder if the table is empty.
        $signatory = Signatory::where('role_type', 'hrmo_designate')
                              ->where('is_active', true)
                              ->first();

        // ── Month label ───────────────────────────────────────────────────
        $months = [
            '', 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        $periodLabel = ($months[$payroll->period_month] ?? '') . ' ' . $payroll->period_year;

        // ── Build per-employee payslip data ───────────────────────────────
        //
        // Each item in $payslips carries:
        //   employee, entry1st, entry2nd, ded1st (keyed collection), ded2nd
        //
        // For consolidated mode:
        //   - if this is the 1st cut-off batch, entry1st = current, entry2nd = sibling
        //   - if this is the 2nd cut-off batch, entry1st = sibling, entry2nd = current
        //
        // For per_batch mode:
        //   - only the current cut-off column is populated; the other is null.

        $siblingEntriesById = $sibling
            ? $sibling->entries()
                       ->with('deductions.deductionType')
                       ->get()
                       ->keyBy('employee_id')
            : collect();

        $dedMap = fn ($entry) => $entry
            ? $entry->deductions->keyBy(fn ($d) => $d->deductionType->code ?? $d->name)
            : collect();

        $payslips = $entries->map(function ($entry) use ($payroll, $siblingEntriesById, $dedMap, $mode) {
            $siblingEntry = $siblingEntriesById->get($entry->employee_id);

            if ($mode === 'consolidated') {
                if ($payroll->cutoff === '1st') {
                    $entry1st = $entry;
                    $entry2nd = $siblingEntry;
                } else {
                    $entry1st = $siblingEntry;
                    $entry2nd = $entry;
                }
            } else {
                // per_batch — only show the column that belongs to this batch
                if ($payroll->cutoff === '1st') {
                    $entry1st = $entry;
                    $entry2nd = null;
                } else {
                    $entry1st = null;
                    $entry2nd = $entry;
                }
            }

            return [
                'employee' => $entry->employee,
                'entry1st' => $entry1st,
                'entry2nd' => $entry2nd,
                'ded1st'   => $dedMap($entry1st),
                'ded2nd'   => $dedMap($entry2nd),
            ];
        });

        // ── Render & stream PDF ───────────────────────────────────────────
        $pdf = Pdf::loadView('payroll.payslip', [
            'batch'       => $payroll,
            'payslips'    => $payslips,
            'rows'        => $this->payslipRows(),
            'periodLabel' => $periodLabel,
            'signatory'   => $signatory,
            'mode'        => $mode,
        ])->setPaper('a4', 'portrait');

        $cutoffLabel = $mode === 'consolidated' ? 'Monthly' : ucfirst($payroll->cutoff) . 'Cutoff';
        $filename    = 'Payslips_' . str_replace(' ', '_', $periodLabel) . '_' . $cutoffLabel . '.pdf';

        return $pdf->stream($filename);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Helpers
    // ═══════════════════════════════════════════════════════════════════

    private function authorizeRole(array $roles): void
    {
        if (! Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }

    private function log(PayrollBatch $batch, string $action, ?string $old, ?string $new): void
    {
        PayrollAuditLog::create([
            'payroll_batch_id' => $batch->id,
            'user_id'          => Auth::id(),
            'action'           => $action,
            'old_value'        => $old,
            'new_value'        => $new,
            'ip_address'       => request()->ip(),
        ]);
    }

    /**
     * Row definitions for the payslip Blade template.
     *
     * Each row is an array with:
     *   type  — income | spacer | deduction | sub | divider | net
     *   label — display label
     *   code  — deduction type code (used for deduction/sub rows only)
     *
     * This is intentionally kept as a simple data structure so the Blade
     * template remains logic-free. Adjust the order and codes to match
     * your DeductionType.code values in the database.
     */
    private function payslipRows(): array
    {
        return [
            // ── Earnings ─────────────────────────────────────────────────
            ['type' => 'spacer',  'label' => 'EARNINGS',              'code' => null],
            ['type' => 'income',  'label' => 'BASIC',                 'code' => null],
            ['type' => 'income',  'label' => 'ALLOWANCE',             'code' => null],  // PERA

            // ── Mandatory Deductions ──────────────────────────────────────
            ['type' => 'spacer',     'label' => 'MANDATORY DEDUCTIONS', 'code' => null],
            ['type' => 'deduction',  'label' => 'GSIS — Life Insurance', 'code' => 'GSIS_LIFE'],
            ['type' => 'deduction',  'label' => 'GSIS — Retirement',     'code' => 'GSIS_RET'],
            ['type' => 'deduction',  'label' => 'PhilHealth',            'code' => 'PHIC'],
            ['type' => 'deduction',  'label' => 'Pag-IBIG / HDMF',      'code' => 'HDMF'],
            ['type' => 'deduction',  'label' => 'Withholding Tax',       'code' => 'TAX'],

            // ── Loans ─────────────────────────────────────────────────────
            ['type' => 'spacer',     'label' => 'LOANS',              'code' => null],
            ['type' => 'deduction',  'label' => 'GSIS Policy Loan',   'code' => 'GSIS_POL'],
            ['type' => 'deduction',  'label' => 'GSIS Emergency Loan','code' => 'GSIS_EML'],
            ['type' => 'sub',        'label' => 'GSIS Consolid. Loan','code' => 'GSIS_CON'],
            ['type' => 'deduction',  'label' => 'Pag-IBIG MP2',       'code' => 'HDMF_MP2'],
            ['type' => 'deduction',  'label' => 'Pag-IBIG Loan',      'code' => 'HDMF_LOAN'],
            ['type' => 'deduction',  'label' => 'LBP Loan',           'code' => 'LBP'],

            // ── Others ────────────────────────────────────────────────────
            ['type' => 'spacer',     'label' => 'OTHERS',             'code' => null],
            ['type' => 'deduction',  'label' => 'CARESS — Union',     'code' => 'CARESS_U'],
            ['type' => 'deduction',  'label' => 'CARESS — Mortuary',  'code' => 'CARESS_M'],
            ['type' => 'deduction',  'label' => 'MASS',               'code' => 'MASS'],
            ['type' => 'deduction',  'label' => 'Provident Fund',     'code' => 'PROVIDENT'],

            // ── Totals & Net ──────────────────────────────────────────────
            ['type' => 'divider', 'label' => 'TOTAL DEDUCTIONS',      'code' => null],
            ['type' => 'net',     'label' => 'NET PAY 1-15',          'code' => null],
            ['type' => 'net',     'label' => 'NET PAY 16-31',         'code' => null],
        ];
    }
}

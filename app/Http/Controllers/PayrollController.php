<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComputePayrollRequest;
use App\Models\AttendanceSnapshot;
use App\Models\PayrollBatch;
use App\Models\PayrollAuditLog;
use App\Services\AttendanceService;
use App\Services\PayrollComputationService;
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
        $query = PayrollBatch::with('creator')
            ->withCount('entries')
            ->withSum('entries', 'gross_income')
            ->withSum('entries', 'total_deductions')
            ->withSum('entries', 'net_amount')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id');

        if ($request->filled('year'))   $query->where('period_year',  $request->year);
        if ($request->filled('month'))  $query->where('period_month', $request->month);
        if ($request->filled('status')) $query->where('status',       $request->status);

        $batches = $query->paginate(15)->withQueryString();

        return view('payroll.index', compact('batches'));
    }

    public function create()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

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
        $payroll->load(['entries.employee', 'entries.deductions', 'creator', 'auditLogs.user']);

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
    //  NEW: Pull Attendance from HRIS API and store snapshots
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
    //  Compute — now reads from snapshots, not live API
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
    //  Approval pipeline (unchanged)
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
            // Also delete attendance snapshots for this batch
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

    // ── Helpers ───────────────────────────────────────────────────────

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
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComputePayrollRequest;
use App\Models\PayrollBatch;
use App\Models\PayrollAuditLog;
use App\Policies\PayrollPolicy;
use App\Services\PayrollComputationService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // Display labels for the payroll pipeline statuses — used in views only,
    // transition logic lives in PayrollPolicy and the individual action methods
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

        // 1st cut-off covers days 1–15; 2nd covers day 16 to end of month
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
            ->with('success', "Payroll batch created for {$request->periodLabel()}. Click 'Compute' to calculate all entries.");
    }

    public function show(PayrollBatch $payroll)
    {
        $payroll->load(['entries.employee', 'entries.deductions', 'creator', 'auditLogs.user']);

        $entries       = $payroll->entries->sortBy(fn ($e) => $e->employee->last_name);
        $totalGross    = $payroll->entries->sum('gross_income');
        $totalDeds     = $payroll->entries->sum('total_deductions');
        $totalNet      = $payroll->entries->sum('net_amount');
        $employeeCount = $payroll->entries->count();
        $auditLogs     = $payroll->auditLogs->sortByDesc('performed_at');

        return view('payroll.show', compact(
            'payroll', 'entries',
            'totalGross', 'totalDeds', 'totalNet', 'employeeCount',
            'auditLogs'
        ));
    }

    public function compute(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('compute', $payroll);

        $attendanceMap = app(AttendanceService::class)->getAttendanceForBatch($payroll);
        $result        = app(PayrollComputationService::class)->computeBatch($payroll, $attendanceMap);

        // Status only advances on the first compute run; re-computing a computed batch
        // recalculates entries without resetting downstream workflow state
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

    // Pipeline transition: HR → Accountant review
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

    // Pipeline transition: Accountant certifies funds → RD/ARD
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

    // Pipeline transition: RD/ARD approves → released for disbursement
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

    // Pipeline transition: Cashier locks after disbursement — no further edits allowed
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

    /**
     * Delete a draft payroll batch along with all its entries, deductions, and audit logs.
     * The policy gate ensures only draft batches can be deleted.
     */
    public function destroy(PayrollBatch $payroll)
    {
        $this->authorize('delete', $payroll);

        DB::transaction(function () use ($payroll) {
            foreach ($payroll->entries as $entry) {
                $entry->deductions()->delete();
            }
            $payroll->entries()->delete();
            $payroll->auditLogs()->delete();
            $payroll->delete();
        });

        return redirect()->route('payroll.index')
            ->with('success', 'Draft payroll batch deleted.');
    }

    /**
     * Side-by-side net pay verification view, equivalent to the "New Net Pay" sheet.
     *
     * Pairs each entry in the current batch with its counterpart in the sibling
     * cut-off batch (same period, opposite cut-off) so reviewers can see both
     * halves of the month at once. Flags employees whose net pay in either
     * cut-off falls below ₱5,000, and highlights entries with an LBP loan deduction.
     */
    public function verify(PayrollBatch $payroll)
    {
        $this->authorize('view', $payroll);

        $payroll->load(['entries.employee', 'entries.deductions']);

        // Fetch the sibling batch (same period, opposite cut-off) for comparison
        $siblingCutoff = $payroll->cutoff === '1st' ? '2nd' : '1st';
        $siblingBatch  = PayrollBatch::with(['entries.employee', 'entries.deductions'])
            ->where('period_year',  $payroll->period_year)
            ->where('period_month', $payroll->period_month)
            ->where('cutoff',       $siblingCutoff)
            ->first();

        // Key sibling entries by employee_id for O(1) lookup inside the map below
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
                    // Flagged if either cut-off net drops below the ₱5,000 minimum threshold
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
            'payroll',
            'siblingBatch',
            'verifyRows',
            'totalNet1st',
            'totalNet2nd',
            'totalCombined',
            'belowThresholdCount'
        ));
    }

    /**
     * Override a locked batch back to released so corrections can be made.
     * Requires a mandatory remarks justification (min 10 chars) for the audit trail.
     */
    public function forceEdit(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('forceEdit', $payroll);

        $request->validate([
            'remarks' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $old = $payroll->status;

        $payroll->update([
            'status'  => 'released',
            'remarks' => $request->input('remarks'),
        ]);

        $this->log($payroll, 'Force Edit Override', $old, 'released');

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll batch unlocked. Status reverted to Released for corrections.');
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Role gate for actions not yet covered by a dedicated policy.
     */
    private function authorizeRole(array $roles): void
    {
        if (! Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }

    /**
     * Write an immutable audit log entry for any payroll batch state change.
     */
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
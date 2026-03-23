<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComputePayrollRequest;
use App\Models\PayrollBatch;
use App\Models\PayrollAuditLog;
use App\Services\PayrollComputationService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // ── Status transition map ─────────────────────────────────────────────
    //  Each role may advance the batch to the next status.
    const APPROVAL_CHAIN = [
        // current status          => [ role_allowed, next_status, label ]
        'draft'                    => ['payroll_officer|hrmo',  'pending_accountant',  'Submit for Accountant Review'],
        'pending_accountant'       => ['accountant',            'pending_rd',          'Certify Funds Available'],
        'pending_rd'               => ['ard',                   'released',            'Approve & Release'],
        'released'                 => ['cashier',               'locked',              'Lock (Disbursed)'],
    ];

    // ── index ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = PayrollBatch::with('creator')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id');

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }
        if ($request->filled('month')) {
            $query->where('period_month', $request->month);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->paginate(15)->withQueryString();

        return view('payroll.index', compact('batches'));
    }

    // ── create (show form) ────────────────────────────────────────────────
    public function create()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $currentYear  = now()->year;
        $currentMonth = now()->month;
        $years        = range($currentYear - 2, $currentYear + 1);

        return view('payroll.create', compact('currentYear', 'currentMonth', 'years'));
    }

    // ── store (create batch + run computation immediately) ────────────────
// ── store (create batch + run computation immediately) ────────────────
public function store(ComputePayrollRequest $request)
{
    $year   = (int) $request->period_year;
    $month  = (int) $request->period_month;
    $cutoff = $request->cutoff;

    // Prevent duplicate batches
    $exists = PayrollBatch::where([
        'period_year'  => $year,
        'period_month' => $month,
        'cutoff'       => $cutoff,
    ])->exists();

    if ($exists) {
        return back()
            ->withInput()
            ->with('error', "A {$cutoff} cut-off payroll batch for {$request->periodLabel()} already exists.");
    }

    // Compute period_start and period_end from year/month/cutoff
    $periodStart = $cutoff === '1st'
        ? \Carbon\Carbon::create($year, $month, 1)
        : \Carbon\Carbon::create($year, $month, 16);

    $periodEnd = $cutoff === '1st'
        ? \Carbon\Carbon::create($year, $month, 15)
        : \Carbon\Carbon::create($year, $month)->endOfMonth();

    // Create the batch record
    $batch = PayrollBatch::create([
        'period_year'  => $year,
        'period_month' => $month,
        'cutoff'       => $cutoff,
        'period_start' => $periodStart->toDateString(),
        'period_end'   => $periodEnd->toDateString(),
        'status'       => 'draft',
        'created_by'   => Auth::id(),
    ]);

    // Log creation
    $this->log($batch, 'created', null, 'draft');

    return redirect()
        ->route('payroll.show', $batch)
        ->with('success', "Payroll batch created for {$request->periodLabel()}. Click 'Compute' to calculate all entries.");
}

    // ── show (batch detail + entries list) ───────────────────────────────
    public function show(PayrollBatch $payroll)
    {
        $payroll->load(['entries.employee', 'entries.deductions', 'creator']);

        $entries     = $payroll->entries->sortBy(fn ($e) => $e->employee->last_name);
        $totalGross  = $payroll->entries->sum('gross_income');
        $totalDeds   = $payroll->entries->sum('total_deductions');
        $totalNet    = $payroll->entries->sum('net_amount');
        $employeeCount = $payroll->entries->count();

        // Determine the next approval action for the current user
        $nextAction = $this->getNextAction($payroll);

        return view('payroll.show', compact(
            'payroll', 'entries',
            'totalGross', 'totalDeds', 'totalNet', 'employeeCount',
            'nextAction'
        ));
    }

    // ── compute (POST — triggers PayrollComputationService for all employees) ──
    public function compute(Request $request, PayrollBatch $payroll)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        if ($payroll->status === 'locked') {
            return back()->with('error', 'This payroll batch is locked and cannot be recomputed.');
        }

        /** @var PayrollComputationService $service */
        $service = app(PayrollComputationService::class);

        // Fetch attendance snapshot from HRIS API (or use empty array for manual)
        /** @var AttendanceService $attendance */
        $attendance    = app(AttendanceService::class);
        $attendanceMap = $attendance->getAttendanceForBatch($payroll);

        $result = $service->computeBatch($payroll, $attendanceMap);

        // Update batch status to 'computed' if it was 'draft'
        if ($payroll->status === 'draft') {
            $payroll->update(['status' => 'computed']);
            $this->log($payroll, 'computed', 'draft', 'computed');
        }

        $message = "Computation complete: {$result['computed']} employee(s) processed.";
        if (!empty($result['errors'])) {
            $errList = implode('; ', $result['errors']);
            return redirect()
                ->route('payroll.show', $payroll)
                ->with('warning', "{$message} Errors: {$errList}");
        }

        return redirect()
            ->route('payroll.show', $payroll)
            ->with('success', $message);
    }

    // ── approve (advance through approval chain) ─────────────────────────
public function approve(Request $request, PayrollBatch $payroll)
{
    // ADD THIS LINE — 'computed' advances the same as 'draft'
    $currentStatus = $payroll->status === 'computed' ? 'draft' : $payroll->status;
    
    $chain = self::APPROVAL_CHAIN[$currentStatus] ?? null;  // ← use $currentStatus, not $payroll->status

    if (!$chain) {
        return back()->with('error', 'This batch cannot be advanced from its current status.');
    }

    [$allowedRoles, $nextStatus, $label] = $chain;

    $roles = explode('|', $allowedRoles);
    if (!Auth::user()->hasAnyRole($roles)) {
        abort(403, "Only {$allowedRoles} may perform this action.");
    }

    $old = $payroll->status;
    $payroll->update([
        'status'      => $nextStatus,
        'approved_by' => Auth::id(),
    ]);

    if ($nextStatus === 'released') {
        $payroll->update(['released_at' => now()]);
    }

    $this->log($payroll, $label, $old, $nextStatus);

    return redirect()
        ->route('payroll.show', $payroll)
        ->with('success', "Payroll batch has been moved to: " . ucfirst(str_replace('_', ' ', $nextStatus)) . ".");
}

    // ── destroy (only drafts) ─────────────────────────────────────────────
    public function destroy(PayrollBatch $payroll)
    {
        $this->authorizeRole(['payroll_officer']);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Only draft batches can be deleted.');
        }

        DB::transaction(function () use ($payroll) {
            foreach ($payroll->entries as $entry) {
                $entry->deductions()->delete();
            }
            $payroll->entries()->delete();
            $payroll->auditLogs()->delete();
            $payroll->delete();
        });

        return redirect()
            ->route('payroll.index')
            ->with('success', 'Draft payroll batch deleted.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  Private helpers
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Return the next action array for the current user on this batch, or null.
     * Shape: [ 'label' => string, 'next_status' => string ]
     */
    private function getNextAction(PayrollBatch $batch): ?array
    {
        // 'computed' shares the same transition as 'draft'
        $status = $batch->status === 'computed' ? 'draft' : $batch->status;
        $chain  = self::APPROVAL_CHAIN[$status] ?? null;

        if (!$chain) return null;

        [$allowedRoles, $nextStatus, $label] = $chain;
        $roles = explode('|', $allowedRoles);

        if (!Auth::user()->hasAnyRole($roles)) return null;

        return compact('label', 'nextStatus');
    }

    /**
     * Quick role gate — abort 403 if user lacks all listed roles.
     */
    private function authorizeRole(array $roles): void
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }

    /**
     * Write an immutable audit log entry.
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

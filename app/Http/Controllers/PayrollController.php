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
    // ─────────────────────────────────────────────────────────────────────
    //  Status labels (for display only — not the transition logic)
    // ─────────────────────────────────────────────────────────────────────
    const STATUS_LABELS = [
        'draft'                => 'Draft',
        'computed'             => 'Computed',
        'pending_accountant'   => 'Pending Accountant',
        'pending_rd'           => 'Pending RD/ARD',
        'released'             => 'Released',
        'locked'               => 'Locked',
    ];

    // ─────────────────────────────────────────────────────────────────────
    //  index
    // ─────────────────────────────────────────────────────────────────────
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
    // statusLabels removed — index.blade.php builds its own map inline
}

    // ─────────────────────────────────────────────────────────────────────
    //  create / store
    // ─────────────────────────────────────────────────────────────────────
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
            ->with('success', "Payroll batch created for {$request->periodLabel()}. Click 'Compute' to calculate all entries.");
    }

    // ─────────────────────────────────────────────────────────────────────
    //  show
    // ─────────────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────────
    //  compute
    // ─────────────────────────────────────────────────────────────────────
    public function compute(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('compute', $payroll);

        /** @var PayrollComputationService $service */
        $service       = app(PayrollComputationService::class);
        $attendance    = app(AttendanceService::class);
        $attendanceMap = $attendance->getAttendanceForBatch($payroll);

        $result = $service->computeBatch($payroll, $attendanceMap);

        if ($payroll->status === 'draft') {
            $payroll->update(['status' => 'computed']);
            $this->log($payroll, 'computed', 'draft', 'computed');
        }

        $message = "Computation complete: {$result['computed']} employee(s) processed.";

        if (!empty($result['errors'])) {
            $errList = implode('; ', $result['errors']);
            return redirect()->route('payroll.show', $payroll)
                ->with('warning', "{$message} Errors: {$errList}");
        }

        return redirect()->route('payroll.show', $payroll)
            ->with('success', $message);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  submit  — HR → Accountant
    //  POST /payroll/{payroll}/submit
    //  draft | computed  →  pending_accountant
    // ─────────────────────────────────────────────────────────────────────
    public function submit(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('submit', $payroll);

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

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

    // ─────────────────────────────────────────────────────────────────────
    //  certify  — Accountant certifies funds → RD/ARD
    //  POST /payroll/{payroll}/certify
    //  pending_accountant  →  pending_rd
    // ─────────────────────────────────────────────────────────────────────
    public function certify(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('certify', $payroll);

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

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

    // ─────────────────────────────────────────────────────────────────────
    //  approve  — RD/ARD approves → released
    //  POST /payroll/{payroll}/approve
    //  pending_rd  →  released
    // ─────────────────────────────────────────────────────────────────────
    public function approve(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('approve', $payroll);

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

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

    // ─────────────────────────────────────────────────────────────────────
    //  lock  — Cashier locks after disbursement
    //  POST /payroll/{payroll}/lock
    //  released  →  locked
    // ─────────────────────────────────────────────────────────────────────
    public function lock(Request $request, PayrollBatch $payroll)
    {
        $this->authorize('lock', $payroll);

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

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

    // ─────────────────────────────────────────────────────────────────────
    //  destroy  — delete draft only
    // ─────────────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Quick role gate for actions not yet covered by policy.
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
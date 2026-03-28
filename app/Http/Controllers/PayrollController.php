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

     // ─────────────────────────────────────────────────────────────────────
    //  verify  — Net Pay Verification (New Net Pay sheet equivalent)
    //  GET /payroll/{payroll}/verify
    // ─────────────────────────────────────────────────────────────────────
    public function verify(PayrollBatch $payroll)
    {
        $this->authorize('view', $payroll);
 
        $payroll->load(['entries.employee', 'entries.deductions']);
 
        // Find the sibling batch (same period, opposite cut-off)
        $siblingCutoff = $payroll->cutoff === '1st' ? '2nd' : '1st';
        $siblingBatch  = PayrollBatch::with(['entries.employee', 'entries.deductions'])
            ->where('period_year',  $payroll->period_year)
            ->where('period_month', $payroll->period_month)
            ->where('cutoff',       $siblingCutoff)
            ->first();
 
        // Index sibling entries by employee_id for O(1) lookup
        $siblingEntries = collect();
        if ($siblingBatch) {
            $siblingEntries = $siblingBatch->entries->keyBy('employee_id');
        }
 
        // Build verify rows — one per employee in the current batch
        $verifyRows = $payroll->entries
            ->sortBy(function ($entry) {
                return optional($entry->employee)->last_name . optional($entry->employee)->first_name;
            })
            ->map(function ($entry) use ($siblingEntries) {
                $employee      = $entry->employee;
                $entrySibling  = $siblingEntries->get($entry->employee_id);
 
                $netCurrent = (float) $entry->net_amount;
                $netSibling = $entrySibling ? (float) $entrySibling->net_amount : null;
                $totalNet   = $netCurrent + ($netSibling ?? 0);
 
                // Check LBP Loan deduction in current entry
                $hasLbpLoan = $entry->deductions
                    ->contains(function ($ded) {
                        return stripos($ded->code ?? '', 'lbp') !== false
                            || stripos($ded->name ?? '', 'lbp') !== false;
                    });
 
                // Flag if either cut-off net is below ₱5,000
                $belowThreshold = $netCurrent < 5000
                    || ($netSibling !== null && $netSibling < 5000);
 
                return (object) [
                    'employee'        => $employee,
                    'entry_current'   => $entry,
                    'entry_sibling'   => $entrySibling,
                    'net_current'     => $netCurrent,
                    'net_sibling'     => $netSibling,
                    'total_net'       => $totalNet,
                    'has_lbp_loan'    => $hasLbpLoan,
                    'below_threshold' => $belowThreshold,
                ];
            })
            ->values();
 
        // Determine which cut-off is 1st and which is 2nd for totals
        if ($payroll->cutoff === '1st') {
            $totalNet1st    = $payroll->entries->sum('net_amount');
            $totalNet2nd    = $siblingBatch ? $siblingBatch->entries->sum('net_amount') : 0;
        } else {
            $totalNet2nd    = $payroll->entries->sum('net_amount');
            $totalNet1st    = $siblingBatch ? $siblingBatch->entries->sum('net_amount') : 0;
        }
 
        $totalCombined       = $totalNet1st + $totalNet2nd;
        $belowThresholdCount = $verifyRows->filter(function ($row) {
            return $row->below_threshold;
        })->count();
 
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
 
    // ─────────────────────────────────────────────────────────────────────
    //  forceEdit  — Payroll Officer overrides locked status back to released
    //  POST /payroll/{payroll}/force-edit
    // ─────────────────────────────────────────────────────────────────────
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
 
        PayrollAuditLog::create([
            'payroll_batch_id' => $payroll->id,
            'user_id'          => Auth::id(),
            'action'           => 'Force Edit Override',
            'old_value'        => $old,
            'new_value'        => 'released',
            'ip_address'       => $request->ip(),
            // Store remarks in the audit log via the remarks field if the table has one,
            // or append to action string. Adjust if your table has a 'notes' column:
            // 'notes'         => $request->input('remarks'),
        ]);
 
        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll batch unlocked. Status reverted to Released for corrections.');
    }
}
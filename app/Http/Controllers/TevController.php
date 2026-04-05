<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTevRequest;
use App\Models\Employee;
use App\Models\OfficeOrder;
use App\Models\PayrollAuditLog;
use App\Models\PerDiemRate;
use App\Models\TevApprovalLog;
use App\Models\TevCertification;
use App\Models\TevItineraryLine;
use App\Models\TevRequest;
use App\Services\TevComputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TevController extends Controller
{
    public function __construct(private TevComputationService $tevService) {}

    // ─────────────────────────────────────────────────────────────────────
    //  Index  GET /tev
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = TevRequest::with(['employee', 'officeOrder'])->orderByDesc('id');

        if ($request->filled('track'))  $query->where('track', $request->track);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('year'))   $query->whereYear('travel_date_start', $request->year);

        $tevRequests = $query->paginate(20)->withQueryString();
        $currentYear = now()->year;

        return view('tev.index', compact('tevRequests', 'currentYear'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Create  GET /tev/create
    // ─────────────────────────────────────────────────────────────────────
    public function create()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $approvedOrders = OfficeOrder::with('employee')
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->get();

        $perDiemRates = PerDiemRate::all()->groupBy('travel_type');

        return view('tev.create', compact('approvedOrders', 'perDiemRates'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Store  POST /tev
    // ─────────────────────────────────────────────────────────────────────
    public function store(StoreTevRequest $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {
            $tev = TevRequest::create([
                'tev_no'               => $this->tevService->generateTevNo(),
                'office_order_id'      => $validated['office_order_id'],
                'employee_id'          => OfficeOrder::findOrFail($validated['office_order_id'])->employee_id,
                'track'                => $validated['track'],
                'purpose'              => $validated['purpose'],
                'destination'          => $validated['destination'],
                'travel_type'          => $validated['travel_type'],
                'travel_date_start'    => $validated['travel_date_start'],
                'travel_date_end'      => $validated['travel_date_end'],
                'total_other_expenses' => 0,
                'status'               => 'draft',
                'remarks'              => $validated['remarks'] ?? null,
            ]);

            foreach ($validated['lines'] as $line) {
                TevItineraryLine::create([
                    'tev_request_id'      => $tev->id,
                    'travel_date'         => $line['travel_date'],
                    'origin'              => $line['origin'],
                    'destination'         => $line['destination'],
                    'departure_time'      => $line['departure_time'] ?? null,
                    'arrival_time'        => $line['arrival_time'] ?? null,
                    'mode_of_transport'   => $line['mode_of_transport'],
                    'transportation_cost' => $line['transportation_cost'],
                    'per_diem_amount'     => $line['per_diem_amount'],
                    'is_half_day'         => !empty($line['is_half_day']),
                    'remarks'             => $line['remarks'] ?? null,
                ]);
            }

            $this->tevService->computeTotals($tev);

            PayrollAuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'Created TEV: ' . $tev->tev_no,
                'old_value'  => null,
                'new_value'  => 'draft',
                'ip_address' => $request->ip(),
            ]);

            $this->lastCreatedId = $tev->id;
        });

        return redirect()->route('tev.show', $this->lastCreatedId)
            ->with('success', 'TEV created successfully.');
    }

    private int $lastCreatedId;

    // ─────────────────────────────────────────────────────────────────────
    //  Show  GET /tev/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $tev = TevRequest::with([
            'employee',
            'officeOrder',
            'itineraryLines',
            'approvalLogs' => fn($q) => $q->with('user')->orderBy('performed_at'),
            'certification.certifier',
        ])->findOrFail($id);

        [$canApprove, $nextAction] = $this->resolveApproval($tev);

        return view('tev.show', compact('tev', 'canApprove', 'nextAction'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Submit  POST /tev/{tevRequest}/submit
    // ─────────────────────────────────────────────────────────────────────
    public function submit(Request $request, int $tevRequest)
    {
        $tev  = TevRequest::findOrFail($tevRequest);
        $user = Auth::user();

        $isOwner = $tev->employee && $tev->employee->user_id === $user->id;
        $isStaff = $user->hasAnyRole(['payroll_officer', 'hrmo']);

        if (!$isOwner && !$isStaff) abort(403);

        if ($tev->status !== 'draft') {
            return back()->with('error', 'Only draft TEV requests can be submitted.');
        }
        if ($tev->itineraryLines()->count() === 0) {
            return back()->with('error', 'Add at least one itinerary line before submitting.');
        }

        $tev->update([
            'status'       => 'submitted',
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
        ]);

        TevApprovalLog::create([
            'tev_request_id' => $tev->id,
            'user_id'        => Auth::id(),
            'step'           => 'submitted',
            'action'         => 'approved',
            'remarks'        => null,
            'ip_address'     => $request->ip(),
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Submitted TEV: ' . $tev->tev_no,
            'old_value'  => 'draft',
            'new_value'  => 'submitted',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'TEV submitted for approval.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Approve (generic role-based transition)  POST /tev/{tevRequest}/approve
    // ─────────────────────────────────────────────────────────────────────
    public function approve(Request $request, int $tevRequest)
    {
        $tev = TevRequest::findOrFail($tevRequest);
        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        [$newStatus, $stepLabel] = $this->resolveTransition($tev);
        $old = $tev->status;

        $tev->update(['status' => $newStatus]);

        TevApprovalLog::create([
            'tev_request_id' => $tev->id,
            'user_id'        => Auth::id(),
            'step'           => $newStatus,
            'action'         => 'approved',
            'remarks'        => $request->remarks,
            'ip_address'     => $request->ip(),
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $stepLabel . ': ' . $tev->tev_no,
            'old_value'  => $old,
            'new_value'  => $newStatus,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'TEV ' . $stepLabel . ' successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Reject  POST /tev/{tevRequest}/reject
    // ─────────────────────────────────────────────────────────────────────
    public function reject(Request $request, int $tevRequest)
    {
        $tev = TevRequest::findOrFail($tevRequest);

        $request->validate(
            ['remarks' => ['required', 'string', 'max:500']],
            ['remarks.required' => 'A reason is required when rejecting a TEV.']
        );

        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'chief_admin_officer', 'cashier']);

        $terminal = ['draft', 'rejected', 'cashier_released', 'reimbursed', 'liquidation_filed', 'liquidated'];
        if (in_array($tev->status, $terminal)) {
            return back()->with('error', 'This TEV cannot be rejected at its current status.');
        }

        $old = $tev->status;
        $tev->update(['status' => 'rejected']);

        TevApprovalLog::create([
            'tev_request_id' => $tev->id,
            'user_id'        => Auth::id(),
            'step'           => 'rejected',
            'action'         => 'rejected',
            'remarks'        => $request->remarks,
            'ip_address'     => $request->ip(),
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Rejected TEV: ' . $tev->tev_no,
            'old_value'  => $old,
            'new_value'  => 'rejected',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tev.show', $tev->id)
            ->with('error', 'TEV has been rejected.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Certify (post-travel certification)  POST /tev/{tevRequest}/certify
    // ─────────────────────────────────────────────────────────────────────
    public function certify(Request $request, int $tevRequest)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $tev = TevRequest::findOrFail($tevRequest);

        $certifiableStatuses = ['rd_approved', 'cashier_released', 'reimbursed', 'liquidation_filed', 'liquidated'];
        if (!in_array($tev->status, $certifiableStatuses)) {
            return back()->with('error', 'TEV must be at rd_approved or later to certify.');
        }

        $data = $request->validate([
            'travel_completed'    => ['nullable', 'boolean'],
            'date_returned'       => ['nullable', 'date'],
            'place_reported_back' => ['nullable', 'string', 'max:100'],
            'annex_a_amount'      => ['nullable', 'numeric', 'min:0'],
            'annex_a_particulars' => ['nullable', 'string'],
            'agency_visited'      => ['nullable', 'string', 'max:255'],
            'appearance_date'     => ['nullable', 'date'],
            'contact_person'      => ['nullable', 'string', 'max:255'],
        ]);

        TevCertification::updateOrCreate(
            ['tev_request_id' => $tev->id],
            array_merge($data, [
                'certified_by'     => Auth::id(),
                'certified_at'     => now(),
                'travel_completed' => !empty($data['travel_completed']),
            ])
        );

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Certified TEV: ' . $tev->tev_no,
            'old_value'  => null,
            'new_value'  => 'certified',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'TEV certification saved.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  File Liquidation  POST /tev/{tevRequest}/liquidate
    //  Employee/payroll officer files actual expenses after CA release
    // ─────────────────────────────────────────────────────────────────────
    public function fileLiquidation(Request $request, int $tevRequest)
    {
        $tev  = TevRequest::findOrFail($tevRequest);
        $user = Auth::user();

        if ($tev->track !== 'cash_advance') {
            return back()->with('error', 'Liquidation only applies to Cash Advance TEVs.');
        }

        if ($tev->status !== 'cashier_released') {
            return back()->with('error', 'Liquidation can only be filed after the cash advance is released.');
        }

        $isOwner = $tev->employee && $tev->employee->user_id === $user->id;
        $isStaff = $user->hasAnyRole(['payroll_officer', 'hrmo']);

        if (!$isOwner && !$isStaff) {
            abort(403, 'You are not authorized to file liquidation for this TEV.');
        }

        $data = $request->validate([
            'actual_amount' => ['required', 'numeric', 'min:0'],
            'remarks'       => ['nullable', 'string', 'max:500'],
        ], [
            'actual_amount.required' => 'Actual amount spent is required.',
            'actual_amount.numeric'  => 'Actual amount must be a valid number.',
            'actual_amount.min'      => 'Actual amount cannot be negative.',
        ]);

        $actualAmount  = (float) $data['actual_amount'];
        $advanceAmount = (float) ($tev->cash_advance_amount ?? $tev->grand_total);

        // Positive = employee owes a refund; Negative = DOLE owes employee additional payment
        $balanceDue = round($advanceAmount - $actualAmount, 2);

        $tev->update([
            'status'              => 'liquidation_filed',
            'cash_advance_amount' => $advanceAmount,
            'balance_due'         => $balanceDue,
        ]);

        TevApprovalLog::create([
            'tev_request_id' => $tev->id,
            'user_id'        => Auth::id(),
            'step'           => 'liquidation_filed',
            'action'         => 'approved',
            'remarks'        => $data['remarks']
                ?? 'Liquidation filed. Actual amount: ₱' . number_format($actualAmount, 2)
                . '. Balance due: ₱' . number_format(abs($balanceDue), 2)
                . ($balanceDue > 0 ? ' (to refund)' : ($balanceDue < 0 ? ' (to claim)' : ' (settled)')),
            'ip_address'     => $request->ip(),
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Filed Liquidation for TEV: ' . $tev->tev_no,
            'old_value'  => 'cashier_released',
            'new_value'  => 'liquidation_filed',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'Liquidation filed successfully. Awaiting cashier approval.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Approve Liquidation  POST /tev/{tevRequest}/liquidation/approve
    //  Cashier finalises the liquidation
    // ─────────────────────────────────────────────────────────────────────
    public function approveLiquidation(Request $request, int $tevRequest)
    {
        $this->authorizeRole(['cashier']);

        $tev = TevRequest::findOrFail($tevRequest);

        if ($tev->track !== 'cash_advance') {
            return back()->with('error', 'Liquidation only applies to Cash Advance TEVs.');
        }

        if ($tev->status !== 'liquidation_filed') {
            return back()->with('error', 'TEV must be in liquidation_filed status to approve.');
        }

        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $tev->update(['status' => 'liquidated']);

        TevApprovalLog::create([
            'tev_request_id' => $tev->id,
            'user_id'        => Auth::id(),
            'step'           => 'liquidated',
            'action'         => 'approved',
            'remarks'        => $data['remarks'] ?? null,
            'ip_address'     => $request->ip(),
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Approved Liquidation for TEV: ' . $tev->tev_no,
            'old_value'  => 'liquidation_filed',
            'new_value'  => 'liquidated',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tev.show', $tev->id)
            ->with('success', 'Liquidation approved. TEV is now fully liquidated.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  destroy() — not permitted
    // ─────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        abort(405);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────

    private function resolveApproval(TevRequest $tev): array
    {
        $user   = Auth::user();
        $status = $tev->status;

        $map = [
            'submitted'            => [['hrmo', 'payroll_officer'],   'HR Approve'],
            'hr_approved'          => [['accountant'],                 'Certify (Accountant)'],
            'accountant_certified' => [['ard', 'chief_admin_officer'], 'RD Approve'],
            'rd_approved'          => [['cashier'],                    $tev->track === 'cash_advance' ? 'Release Cash Advance' : 'Mark Reimbursed'],
            'liquidation_filed'    => [['cashier'],                    'Approve Liquidation'],
        ];

        if (!isset($map[$status])) {
            return [false, ''];
        }

        [$roles, $label] = $map[$status];

        return [$user->hasAnyRole($roles), $label];
    }

    private function resolveTransition(TevRequest $tev): array
    {
        $user   = Auth::user();
        $status = $tev->status;

        if ($status === 'submitted' && $user->hasAnyRole(['hrmo', 'payroll_officer'])) {
            return ['hr_approved', 'HR Approved'];
        }
        if ($status === 'hr_approved' && $user->hasAnyRole(['accountant'])) {
            return ['accountant_certified', 'Accountant Certified'];
        }
        if ($status === 'accountant_certified' && $user->hasAnyRole(['ard', 'chief_admin_officer'])) {
            return ['rd_approved', 'RD Approved'];
        }
        if ($status === 'rd_approved' && $user->hasAnyRole(['cashier'])) {
            $newStatus = $tev->track === 'cash_advance' ? 'cashier_released' : 'reimbursed';
            $label     = $tev->track === 'cash_advance' ? 'Cash Advance Released' : 'Reimbursed';
            return [$newStatus, $label];
        }

        // liquidation_filed → liquidated is handled by approveLiquidation(), not this method
        abort(403, 'You are not authorized to approve this TEV at its current status.');
    }

    private function authorizeRole(array $roles): void
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }
}
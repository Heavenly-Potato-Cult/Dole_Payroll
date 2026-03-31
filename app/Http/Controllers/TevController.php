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
    //  Index
    //  GET /tev
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = TevRequest::with(['employee', 'officeOrder'])
            ->orderByDesc('id');

        if ($request->filled('track')) {
            $query->where('track', $request->track);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('year')) {
            $query->whereYear('travel_date_start', $request->year);
        }

        $tevRequests = $query->paginate(20)->withQueryString();
        $currentYear = now()->year;

        return view('tev.index', compact('tevRequests', 'currentYear'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Create Form
    //  GET /tev/create
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
    //  Store
    //  POST /tev
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

            // Store $tev->id for redirect outside closure
            $this->lastCreatedId = $tev->id;
        });

        return redirect()->route('tev.show', $this->lastCreatedId)
            ->with('success', 'TEV created successfully.');
    }

    // Temporary holder for ID created inside transaction closure
    private int $lastCreatedId;

    // ─────────────────────────────────────────────────────────────────────
    //  Show
    //  GET /tev/{id}
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
    //  Submit (employee submits their own draft)
    //  POST /tev/{tevRequest}/submit
    // ─────────────────────────────────────────────────────────────────────
    public function submit(Request $request, int $tevRequest)
    {
        $tev = TevRequest::findOrFail($tevRequest);

        // Only the employee who owns the TEV (or hrmo/payroll_officer) may submit
        $user = Auth::user();
        $isOwner = $tev->employee && $tev->employee->user_id === $user->id;
        $isStaff = $user->hasAnyRole(['payroll_officer', 'hrmo']);

        if (!$isOwner && !$isStaff) {
            abort(403);
        }

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
    //  Approve (role-based status transition)
    //  POST /tev/{tevRequest}/approve
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
    //  Reject
    //  POST /tev/{tevRequest}/reject
    // ─────────────────────────────────────────────────────────────────────
    public function reject(Request $request, int $tevRequest)
    {
        $tev = TevRequest::findOrFail($tevRequest);

        $request->validate([
            'remarks' => ['required', 'string', 'max:500'],
        ], [
            'remarks.required' => 'A reason is required when rejecting a TEV.',
        ]);

        // Any current approver may reject; validate they have a relevant role
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'chief_admin_officer', 'cashier']);

        $terminal = ['draft', 'rejected', 'cashier_released', 'reimbursed'];
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
    //  Certify (post-travel certification)
    //  POST /tev/{tevRequest}/certify
    // ─────────────────────────────────────────────────────────────────────
    public function certify(Request $request, int $tevRequest)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $tev = TevRequest::findOrFail($tevRequest);

        $certifiableStatuses = ['rd_approved', 'cashier_released', 'reimbursed'];
        if (!in_array($tev->status, $certifiableStatuses)) {
            return back()->with('error', 'TEV must be at rd_approved or later to certify.');
        }

        $data = $request->validate([
            'travel_completed'     => ['nullable', 'boolean'],
            'date_returned'        => ['nullable', 'date'],
            'place_reported_back'  => ['nullable', 'string', 'max:100'],
            'annex_a_amount'       => ['nullable', 'numeric', 'min:0'],
            'annex_a_particulars'  => ['nullable', 'string'],
            'agency_visited'       => ['nullable', 'string', 'max:255'],
            'appearance_date'      => ['nullable', 'date'],
            'contact_person'       => ['nullable', 'string', 'max:255'],
        ]);

        TevCertification::updateOrCreate(
            ['tev_request_id' => $tev->id],
            array_merge($data, [
                'certified_by' => Auth::id(),
                'certified_at' => now(),
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
    //  destroy() — not permitted
    // ─────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        abort(405);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Determine whether the current user can approve and what the next action label is.
     * Returns [bool $canApprove, string $nextAction].
     */
    private function resolveApproval(TevRequest $tev): array
    {
        $user   = Auth::user();
        $status = $tev->status;

        $map = [
            'submitted'            => [['hrmo', 'payroll_officer'],   'HR Approve'],
            'hr_approved'          => [['accountant'],                 'Certify (Accountant)'],
            'accountant_certified' => [['ard', 'chief_admin_officer'], 'RD Approve'],
            'rd_approved'          => [['cashier'],                    $tev->track === 'cash_advance' ? 'Release Cash Advance' : 'Mark Reimbursed'],
        ];

        if (!isset($map[$status])) {
            return [false, ''];
        }

        [$roles, $label] = $map[$status];

        return [$user->hasAnyRole($roles), $label];
    }

    /**
     * Validate the current user's role matches the required transition,
     * then return [newStatus, actionLabel].
     */
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

        abort(403, 'You are not authorized to approve this TEV at its current status.');
    }

    private function authorizeRole(array $roles): void
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }
}
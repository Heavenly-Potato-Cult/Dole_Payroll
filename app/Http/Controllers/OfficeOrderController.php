<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeOrderRequest;
use App\Models\Employee;
use App\Models\OfficeOrder;
use App\Models\PayrollAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeOrderController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    //  Index
    //  GET /office-orders
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = OfficeOrder::with('employee')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->whereYear('travel_date_start', $request->year);
        }

        $orders      = $query->paginate(20)->withQueryString();
        $currentYear = now()->year;

        return view('office-orders.index', compact('orders', 'currentYear'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Create Form
    //  GET /office-orders/create
    // ─────────────────────────────────────────────────────────────────────
    public function create()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'middle_name', 'position_title']);

        return view('office-orders.create', compact('employees'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Store
    //  POST /office-orders
    // ─────────────────────────────────────────────────────────────────────
    public function store(StoreOfficeOrderRequest $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $order = OfficeOrder::create(array_merge(
            $request->validated(),
            ['status' => 'draft']
        ));

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Created Office Order: ' . $order->office_order_no,
            'old_value'  => null,
            'new_value'  => 'draft',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('office-orders.show', $order->id)
            ->with('success', 'Office Order ' . $order->office_order_no . ' created successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Show
    //  GET /office-orders/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $order = OfficeOrder::with(['employee', 'approver', 'tevRequests.employee'])
            ->findOrFail($id);

        return view('office-orders.show', compact('order'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Edit Form
    //  GET /office-orders/{id}/edit
    // ─────────────────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $order = OfficeOrder::where('status', 'draft')->findOrFail($id);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'middle_name', 'position_title']);

        return view('office-orders.edit', compact('order', 'employees'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Update
    //  PUT /office-orders/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function update(StoreOfficeOrderRequest $request, int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $order = OfficeOrder::where('status', 'draft')->findOrFail($id);

        // Re-validate unique ignoring current record
        $request->validate([
            'office_order_no' => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('office_orders', 'office_order_no')
                    ->ignore($order->id),
            ],
        ]);

        $order->update($request->validated());

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Updated Office Order: ' . $order->office_order_no,
            'old_value'  => null,
            'new_value'  => 'draft',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('office-orders.show', $order->id)
            ->with('success', 'Office Order updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Approve
    //  POST /office-orders/{id}/approve
    // ─────────────────────────────────────────────────────────────────────
    public function approve(Request $request, int $id)
    {
        $this->authorizeRole(['hrmo', 'ard', 'chief_admin_officer']);

        $order = OfficeOrder::findOrFail($id);

        if ($order->status !== 'draft') {
            return back()->with('error', 'Only draft Office Orders can be approved.');
        }

        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $old = $order->status;

        $order->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks'     => $request->remarks ?? $order->remarks,
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Approved Office Order: ' . $order->office_order_no,
            'old_value'  => $old,
            'new_value'  => 'approved',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('office-orders.show', $order->id)
            ->with('success', 'Office Order approved successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Cancel
    //  POST /office-orders/{id}/cancel
    // ─────────────────────────────────────────────────────────────────────
    public function cancel(Request $request, int $id)
    {
        $this->authorizeRole(['hrmo', 'ard', 'chief_admin_officer']);

        $order = OfficeOrder::withCount('tevRequests')->findOrFail($id);

        if ($order->tev_requests_count > 0) {
            return back()->with('error', 'Cannot cancel: this Office Order has linked TEV requests.');
        }

        if ($order->status === 'cancelled') {
            return back()->with('error', 'Office Order is already cancelled.');
        }

        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $old = $order->status;

        $order->update([
            'status'  => 'cancelled',
            'remarks' => $request->remarks ?? $order->remarks,
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Cancelled Office Order: ' . $order->office_order_no,
            'old_value'  => $old,
            'new_value'  => 'cancelled',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('office-orders.show', $order->id)
            ->with('success', 'Office Order cancelled.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Destroy (not used via resource — kept for completeness)
    // ─────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        // Intentionally left unimplemented.
        // Soft-delete via cancel(); hard delete not permitted.
        abort(405);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────
    private function authorizeRole(array $roles): void
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }
}
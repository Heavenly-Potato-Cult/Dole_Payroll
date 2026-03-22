<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // ── Index ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search     = $request->input('search');
        $divisionId = $request->input('division_id');
        $status     = $request->input('status');

        $employees = Employee::query()
            ->with('division')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('last_name',         'like', "%{$search}%")
                       ->orWhere('first_name',      'like', "%{$search}%")
                       ->orWhere('plantilla_item_no','like', "%{$search}%")
                       ->orWhere('position_title',  'like', "%{$search}%");
                });
            })
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->when($status,     fn ($q) => $q->where('status', $status))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $divisions = Division::orderBy('name')->get(['id', 'name', 'code']);

        return view('employees.index', compact('employees', 'divisions', 'search', 'divisionId', 'status'));
    }

    // ── Create ───────────────────────────────────────────────────
    public function create()
    {
        $divisions = Division::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $sitYears  = [2022, 2021]; // latest first
        $latestYear = 2022;

        return view('employees.create', compact('divisions', 'sitYears', 'latestYear'));
    }

    // ── Store ────────────────────────────────────────────────────
    public function store(StoreEmployeeRequest $request)
    {
        // Strip commas from formatted salary before saving
        $data = $request->validated();
        $data['basic_salary'] = str_replace(',', '', $data['basic_salary']);

        Employee::create($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee record created successfully.');
    }

    // ── Show ─────────────────────────────────────────────────────
    public function show(Employee $employee)
    {
        $employee->load(['division', 'promotionHistory', 'deductions']);
        return view('employees.show', compact('employee'));
    }

    // ── Edit ─────────────────────────────────────────────────────
    public function edit(Employee $employee)
    {
        $divisions  = Division::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $sitYears   = [2022, 2021];

        return view('employees.edit', compact('employee', 'divisions', 'sitYears'));
    }

    // ── Update ───────────────────────────────────────────────────
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $data['basic_salary'] = str_replace(',', '', $data['basic_salary']);

        $employee->update($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee record updated successfully.');
    }

    // ── Destroy ──────────────────────────────────────────────────
    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->delete(); // soft delete

        return redirect()->route('employees.index')
            ->with('success', "Employee \"{$name}\" removed from the active plantilla.");
    }

    // ── Deductions (stub — full module in Phase 2) ────────────────
    public function deductions(Employee $employee)
    {
        $employee->load('deductions');
        return view('employees.deductions', compact('employee'));
    }

    public function updateDeductions(Request $request, Employee $employee)
    {
        // Placeholder — full logic in Phase 2 deductions module
        return redirect()->route('employees.show', $employee)
            ->with('success', 'Deductions updated.');
    }
}
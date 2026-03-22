<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\EmployeeDeductionEnrollment;
use Illuminate\Http\Request;

class EmployeeDeductionController extends Controller
{
    /**
     * GET /employees/{employee}/deductions
     * Show all deduction enrollments for an employee.
     */
    public function index(Employee $employee)
    {
        $employee->load(['division']);

        // All active deduction types, ordered for display
        $deductionTypes = DeductionType::active()->ordered()->get();

        // Current enrollments keyed by deduction_type_id for easy lookup in blade
        $enrollments = EmployeeDeductionEnrollment::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->with('deductionType')
            ->get()
            ->keyBy('deduction_type_id');

        return view('employees.deductions', compact('employee', 'deductionTypes', 'enrollments'));
    }

    /**
     * POST /employees/{employee}/deductions
     * Save the full deduction enrollment form (bulk upsert).
     *
     * Form sends:
     *   deductions[{deduction_type_id}][enrolled]  = 1|0
     *   deductions[{deduction_type_id}][amount]    = numeric
     *   deductions[{deduction_type_id}][effective_from] = date
     *   deductions[{deduction_type_id}][effective_to]   = date|null
     *   deductions[{deduction_type_id}][notes]     = string|null
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'deductions'                         => 'nullable|array',
            'deductions.*.amount'                => 'nullable|numeric|min:0',
            'deductions.*.effective_from'        => 'nullable|date',
            'deductions.*.effective_to'          => 'nullable|date|after_or_equal:deductions.*.effective_from',
        ]);

        $submitted = $request->input('deductions', []);

        foreach ($submitted as $typeId => $data) {
            $enrolled = ! empty($data['enrolled']);
            $amount   = $enrolled ? ($data['amount'] ?? 0) : 0;

            // Skip computed deductions — amounts are set by the payroll engine, not HR
            $type = DeductionType::find($typeId);
            if (! $type || $type->is_computed) continue;

            $existing = EmployeeDeductionEnrollment::where('employee_id', $employee->id)
                ->where('deduction_type_id', $typeId)
                ->first();

            if ($enrolled && $amount > 0) {
                EmployeeDeductionEnrollment::updateOrCreate(
                    [
                        'employee_id'       => $employee->id,
                        'deduction_type_id' => $typeId,
                    ],
                    [
                        'amount'         => $amount,
                        'effective_from' => $data['effective_from'] ?? now()->startOfMonth()->toDateString(),
                        'effective_to'   => $data['effective_to'] ?: null,
                        'is_active'      => true,
                        'notes'          => $data['notes'] ?? null,
                    ]
                );
            } elseif (! $enrolled && $existing) {
                // Deactivate rather than delete — preserves audit trail
                $existing->update(['is_active' => false]);
            }
        }

        return redirect()->route('employees.deductions', $employee)
            ->with('success', 'Deductions updated for ' . $employee->full_name . '.');
    }
}
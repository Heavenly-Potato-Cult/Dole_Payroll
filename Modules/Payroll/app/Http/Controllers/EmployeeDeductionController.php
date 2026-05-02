<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Payroll\Models\DeductionType;
use App\SharedKernel\Models\Employee;
use Modules\Payroll\Models\EmployeeDeductionEnrollment;
use Illuminate\Http\Request;

class EmployeeDeductionController extends Controller
{
    /**
     * Show all deduction enrollments for an employee.
     *
     * Enrollments are keyed by deduction_type_id so the Blade view can do
     * a direct lookup ($enrollments[$type->id]) instead of searching a flat list.
     */
    public function index(Employee $employee)
    {
        $employee->load(['division']);

        $deductionTypes = DeductionType::active()->ordered()->get();

        $enrollments = EmployeeDeductionEnrollment::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->with('deductionType')
            ->get()
            ->keyBy('deduction_type_id');

        return view('payroll::employees.deductions', compact('employee', 'deductionTypes', 'enrollments'));
    }

    /**
     * Bulk-upsert deduction enrollments from the deductions form.
     *
     * Each entry in the `deductions` array is keyed by deduction_type_id:
     *   deductions[{id}][enrolled]       = 1|0
     *   deductions[{id}][amount]         = numeric
     *   deductions[{id}][effective_from] = date
     *   deductions[{id}][effective_to]   = date|null
     *   deductions[{id}][notes]          = string|null
     *
     * Computed deduction types (e.g. GSIS, PhilHealth) are skipped here —
     * their amounts are derived by the payroll engine, not set manually by HR.
     *
     * Un-enrolling deactivates the record rather than deleting it
     * to preserve the audit trail.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'deductions'                  => 'nullable|array',
            'deductions.*.amount'         => 'nullable|numeric|min:0',
            'deductions.*.effective_from' => 'nullable|date',
            'deductions.*.effective_to'   => 'nullable|date|after_or_equal:deductions.*.effective_from',
        ]);

        $submitted = $request->input('deductions', []);

        foreach ($submitted as $typeId => $data) {
            $type = DeductionType::find($typeId);

            // Amounts for computed types are owned by the payroll engine
            if (! $type || $type->is_computed) continue;

            $enrolled = ! empty($data['enrolled']);
            $amount   = $enrolled ? ($data['amount'] ?? 0) : 0;

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
            } else {
                // Deactivate rather than delete to preserve audit trail
                EmployeeDeductionEnrollment::where('employee_id', $employee->id)
                    ->where('deduction_type_id', $typeId)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        }

        return redirect()->route('employees.deductions', $employee)
            ->with('success', 'Deductions updated for ' . $employee->full_name . '.');
    }
}

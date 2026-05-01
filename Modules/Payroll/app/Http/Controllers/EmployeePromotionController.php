<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\SharedKernel\Models\Employee;
use Modules\Payroll\Models\EmployeePromotionHistory;
use App\Models\SalaryIndexTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeePromotionController extends Controller
{
    /**
     * Timeline of all promotion and step increment records for an employee.
     */
    public function index(Employee $employee)
    {
        $history = $employee->promotionHistory()
            ->with('createdBy')
            ->orderByDesc('effective_date')
            ->get();

        return view('payroll::employees.promotions.index', compact('employee', 'history'));
    }

    public function create(Employee $employee)
    {
        $sitYears = [2022, 2021];

        return view('payroll::employees.promotions.create', compact('employee', 'sitYears'));
    }

    /**
     * Validate and record a promotion, then update the employee's live SG/Step/Salary.
     *
     * Two business rules enforced beyond standard validation:
     *   1. Only one promotion record is allowed per calendar month.
     *   2. The new salary must be >= the employee's current salary.
     *
     * On success, both the history record and the employee's current record are written
     * atomically — the history row snapshots old values before they are overwritten.
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'effective_date' => [
                'required',
                'date',
                function ($attr, $value, $fail) use ($employee) {
                    $month  = \Carbon\Carbon::parse($value)->format('Y-m');
                    $exists = EmployeePromotionHistory::where('employee_id', $employee->id)
                        ->whereRaw("DATE_FORMAT(effective_date, '%Y-%m') = ?", [$month])
                        ->exists();
                    if ($exists) {
                        $fail("A promotion record already exists for {$month}. Only one per month is allowed.");
                    }
                },
            ],
            'new_sg'     => 'required|integer|min:1|max:33',
            'new_step'   => 'required|integer|min:1|max:8',
            'sit_year'   => 'required|integer|min:2021',
            'new_salary' => 'required|numeric|min:1',
            'type'       => ['required', Rule::in(array_keys(EmployeePromotionHistory::TYPES))],
            'remarks'    => 'nullable|string|max:500',
        ]);

        if ((float) $validated['new_salary'] < (float) $employee->basic_salary) {
            return back()->withErrors([
                'new_salary' => 'New salary (₱' . number_format($validated['new_salary'], 2) . ') '
                    . 'cannot be less than current salary (₱' . number_format($employee->basic_salary, 2) . ').',
            ])->withInput();
        }

        // Snapshot current values into history before overwriting
        EmployeePromotionHistory::create([
            'employee_id'    => $employee->id,
            'effective_date' => $validated['effective_date'],
            'old_sg'         => $employee->salary_grade,
            'old_step'       => $employee->step,
            'old_salary'     => $employee->basic_salary,
            'new_sg'         => $validated['new_sg'],
            'new_step'       => $validated['new_step'],
            'new_salary'     => $validated['new_salary'],
            'type'           => $validated['type'],
            'remarks'        => $validated['remarks'],
            'created_by'     => auth()->id(),
        ]);

        $employee->update([
            'salary_grade'        => $validated['new_sg'],
            'step'                => $validated['new_step'],
            'sit_year'            => $validated['sit_year'],
            'basic_salary'        => $validated['new_salary'],
            'last_promotion_date' => $validated['effective_date'],
        ]);

        return redirect()->route('employees.promotions.index', $employee)
            ->with('success', 'Promotion record saved. Employee SG/Step/Salary updated.');
    }

    /**
     * Delete a promotion record and roll back the employee's current SG/Step/Salary.
     *
     * Only the most recent record can be deleted. Removing an older entry would leave
     * the employee's current values inconsistent with the remaining history chain.
     */
    public function destroy(Employee $employee, EmployeePromotionHistory $promotion)
    {
        $latest = EmployeePromotionHistory::where('employee_id', $employee->id)
            ->orderByDesc('effective_date')
            ->first();

        if ($latest && $latest->id !== $promotion->id) {
            return back()->with('error', 'Only the most recent promotion record can be deleted.');
        }

        // Restore the values that were snapshotted when this record was created
        $employee->update([
            'salary_grade' => $promotion->old_sg,
            'step'         => $promotion->old_step,
            'basic_salary' => $promotion->old_salary,
        ]);

        $promotion->delete();

        return redirect()->route('employees.promotions.index', $employee)
            ->with('success', 'Promotion record deleted. Employee salary restored to previous values.');
    }
}

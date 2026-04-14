<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Division;
use App\Models\Employee;
use App\Services\HrisApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $divisions = Division::orderBy('name')->get(['id', 'name', 'code']);
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

    // ── Pull from HRIS API ─────────────────────────────────────────
    public function pullFromApi(Request $request)
    {
        try {
            $hrisService = app(HrisApiService::class);
            $employees = $hrisService->fetchEmployees();

            if (empty($employees)) {
                return redirect()->route('employees.index')
                    ->with('error', 'No employees returned from HRIS API.');
            }

            Log::info('HRIS sync started', ['total_from_api' => count($employees)]);

            $synced = 0;
            $updated = 0;
            $skippedDivision = 0;
            $skippedPlantilla = 0;
            $usedPlantillas = []; // Track plantilla_item_no used in this sync batch

            foreach ($employees as $empData) {
                // Match division by code from API to local division
                $divisionCode = $empData['division_code'] ?? null;
                $division = null;
                if ($divisionCode) {
                    $division = \App\Models\Division::where('code', $divisionCode)->first();
                }

                // Skip if no division match
                if (!$division) {
                    Log::warning('Skipping employee without matching division', [
                        'employee_no' => $empData['employee_no'],
                        'division_code' => $divisionCode,
                    ]);
                    $skippedDivision++;
                    continue;
                }

                // Map API field names to database column names
                $dbData = [
                    'employee_no' => $empData['employee_no'] ?? null,
                    'last_name' => $empData['last_name'],
                    'first_name' => $empData['first_name'],
                    'middle_name' => $empData['middle_name'] ?? null,
                    'position_title' => $empData['position_title'],
                    'plantilla_item_no' => $empData['plantilla_item_no'],
                    'salary_grade' => $empData['salary_grade'],
                    'step' => $empData['step'],
                    'basic_salary' => $empData['basic_monthly_salary'],
                    'employment_status' => $empData['employment_status'] ?? 'permanent',
                    'official_station' => $empData['official_station'] ?? null,
                    'original_appointment_date' => $empData['date_original_appointment'] ?? null,
                    'last_promotion_date' => $empData['last_promotion_date'] ?? null,
                    'gsis_bp_no' => $empData['gsis_bp_no'] ?? null,
                    'gsis_crn' => $empData['gsis_crn'] ?? null,
                    'pagibig_no' => $empData['pagibig_mid_no'] ?? null,
                    'philhealth_no' => $empData['philhealth_no'] ?? null,
                    'tin' => $empData['tin'] ?? null,
                    'status' => 'active',
                ];

                // Set division_id
                $dbData['division_id'] = $division->id;

                // Check for existing employee by employee_no only (not plantilla_item_no)
                $employee = Employee::where('employee_no', $dbData['employee_no'])->first();

                if ($employee) {
                    // Update existing employee - don't change plantilla_item_no
                    $updateData = $dbData;
                    unset($updateData['plantilla_item_no']);
                    $employee->update($updateData);
                    $updated++;
                } else {
                    // Set default hire_date if not provided
                    $dbData['hire_date'] = $empData['date_original_appointment'] ?? now();
                    Employee::create($dbData);
                    $synced++;
                }
            }

            Log::info('HRIS sync completed', [
                'synced' => $synced,
                'updated' => $updated,
                'skipped_division' => $skippedDivision,
                'skipped_plantilla' => $skippedPlantilla,
            ]);

            return redirect()->route('employees.index')
                ->with('success', "Synced {$synced} new employees, updated {$updated} existing employees from HRIS.");

        } catch (\Exception $e) {
            Log::error('HRIS API sync error', ['error' => $e->getMessage()]);
            return redirect()->route('employees.index')
                ->with('error', 'Failed to sync from HRIS: ' . $e->getMessage());
        }
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
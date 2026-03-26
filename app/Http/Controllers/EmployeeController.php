<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;  // ← add this

class EmployeeController extends Controller
{
    // ── Index ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        \Log::info('A - Controller reached: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');
        \DB::enableQueryLog();
        $search     = $request->input('search');
        $divisionId = $request->input('division_id');
        $status     = $request->input('status');
        $page       = $request->input('page', 1);

        $isFiltered = $search || $divisionId || $status;
        \Log::info('B - Before cache get: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');
        if ($isFiltered) {
            // Filtered — hit Aiven directly (dynamic results)
            $employees = Employee::query()
                ->with('division')
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('last_name',          'like', "%{$search}%")
                           ->orWhere('first_name',       'like', "%{$search}%")
                           ->orWhere('plantilla_item_no','like', "%{$search}%")
                           ->orWhere('position_title',   'like', "%{$search}%");
                    });
                })
                ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
                ->when($status,     fn ($q) => $q->where('status', $status))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(20)
                ->withQueryString();
        } else {
            // No filters — serve from Redis ⚡
            $employees = Cache::get("employees.page.{$page}");

            // Fallback to DB if cache is cold
            if (!$employees) {
                $employees = Employee::query()
                    ->with('division')
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->paginate(20);

                Cache::put("employees.page.{$page}", $employees, now()->addMinutes(30));
            }
        }
        \Log::info('C - After employees cache: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');


        // Divisions always from Redis ⚡
        $divisions = Cache::get('divisions.all');

        if (!$divisions) {
            $divisions = Division::orderBy('name')->get(['id', 'name', 'code']);
            Cache::put('divisions.all', $divisions, now()->addHours(2));
        }
        \Log::info('D - After divisions cache: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');
         $queries = \DB::getQueryLog();
        \Log::info('Employee index queries: ' . count($queries), $queries);

        return view('employees.index', compact('employees', 'divisions', 'search', 'divisionId', 'status'));
    }

    // ── Create ───────────────────────────────────────────────────
    public function create()
    {
        // Divisions from Redis ⚡
        $divisions = Cache::get('divisions.all') 
            ?? Division::orderBy('name')->get(['id', 'name', 'code']);

        $sitYears   = [2022, 2021];
        $latestYear = 2022;

        return view('employees.create', compact('divisions', 'sitYears', 'latestYear'));
    }

    // ── Store ────────────────────────────────────────────────────
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $data['basic_salary'] = str_replace(',', '', $data['basic_salary']);

        Employee::create($data);

        // Bust cache so WarmCache picks up new employee
        Cache::forget('employees.page.1');

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
        // Divisions from Redis ⚡
        $divisions = Cache::get('divisions.all')
            ?? Division::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        $sitYears = [2022, 2021];

        return view('employees.edit', compact('employee', 'divisions', 'sitYears'));
    }

    // ── Update ───────────────────────────────────────────────────
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $data['basic_salary'] = str_replace(',', '', $data['basic_salary']);

        $employee->update($data);

        // Bust cache
        Cache::forget('employees.page.1');

        return redirect()->route('employees.index')
            ->with('success', 'Employee record updated successfully.');
    }

    // ── Destroy ──────────────────────────────────────────────────
    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->delete();

        // Bust cache
        Cache::forget('employees.page.1');

        return redirect()->route('employees.index')
            ->with('success', "Employee \"{$name}\" removed from the active plantilla.");
    }

    // ── Deductions ────────────────────────────────────────────────
    public function deductions(Employee $employee)
    {
        $employee->load('deductions');
        return view('employees.deductions', compact('employee'));
    }

    public function updateDeductions(Request $request, Employee $employee)
    {
        return redirect()->route('employees.show', $employee)
            ->with('success', 'Deductions updated.');
    }
}

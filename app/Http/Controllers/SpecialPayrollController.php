<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialPayrollRequest;
use App\Models\Employee;
use App\Models\PayrollAuditLog;
use App\Models\SpecialPayrollBatch;
use App\Services\NewlyHiredPayrollService;
use App\Services\SalaryDifferentialService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SpecialPayrollController
 *
 * Handles all special payroll operations including newly hired, salary differential,
 * NOSI, NOSA, and step increment payroll batches.
 *
 * @package App\Http\Controllers
 */
class SpecialPayrollController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    //  Newly Hired — Index
    //  GET /special-payroll/newly-hired
    // ─────────────────────────────────────────────────────────────────────
    public function newHireIndex(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = SpecialPayrollBatch::with('employee')
            ->where('type', 'newly_hired')
            ->orderByDesc('id');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches     = $query->paginate(20)->withQueryString();
        $currentYear = now()->year;

        return view('special-payroll.newly-hired-index', compact('batches', 'currentYear'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Newly Hired — Create Form
    //  GET /special-payroll/newly-hired/create
    // ─────────────────────────────────────────────────────────────────────
    public function newHireCreate()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'middle_name',
                   'position_title', 'basic_salary', 'pera']);

        return view('special-payroll.newly-hired-create', compact('employees'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Newly Hired — Store
    //  POST /special-payroll/newly-hired
    // ─────────────────────────────────────────────────────────────────────
    public function newHireStore(StoreSpecialPayrollRequest $request)
    {
        $employee = Employee::findOrFail($request->employee_id);

        /** @var NewlyHiredPayrollService $service */
        $service = app(NewlyHiredPayrollService::class);

        $result = $service->compute(
            employee:          $employee,
            effectivity_date:  $request->effectivity_date,
            cutoff_start:      $request->cutoff_start,
            cutoff_end:        $request->cutoff_end,
            lwop_days:         (int) ($request->lwop_days ?? 0),
            tardiness_minutes: 0
        );

        $cutoffStart = Carbon::parse($request->cutoff_start);
        $effectivity = Carbon::parse($request->effectivity_date);

        $title = 'Pro-Rated Payroll — '
            . $employee->last_name . ', ' . $employee->first_name
            . ' (' . $effectivity->format('M d, Y') . ')';

        $batch = SpecialPayrollBatch::create([
            'type'              => 'newly_hired',
            'title'             => $title,
            'year'              => $cutoffStart->year,
            'month'             => $cutoffStart->month,
            'effectivity_date'  => $request->effectivity_date,
            'period_start'      => $request->cutoff_start,
            'period_end'        => $request->cutoff_end,
            'employee_id'       => $employee->id,
            'pro_rated_days'    => $result['working_days'],
            'gross_amount'      => $result['net_earned'],
            'deductions_amount' => $result['total_deductions'],
            'net_amount'        => $result['net_amount'],
            'status'            => 'draft',
            'remarks'           => $request->remarks,
        ]);

        session(['newly_hired_result_' . $batch->id => $result]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Created Newly Hired Pro-Rated Payroll: ' . $employee->last_name . ', ' . $employee->first_name,
            'old_value'  => null,
            'new_value'  => 'draft',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('special-payroll.newly-hired.show', $batch->id)
            ->with('success', "Pro-rated payroll created for {$employee->last_name}, {$employee->first_name}.");
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Newly Hired — Show
    //  GET /special-payroll/newly-hired/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function newHireShow(int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $batch    = SpecialPayrollBatch::with('employee', 'approver')
            ->where('type', 'newly_hired')
            ->findOrFail($id);

        $employee = $batch->employee;

        /** @var NewlyHiredPayrollService $service */
        $service = app(NewlyHiredPayrollService::class);

        $result = $service->compute(
            employee:          $employee,
            effectivity_date:  $batch->effectivity_date->toDateString(),
            cutoff_start:      $batch->period_start->toDateString(),
            cutoff_end:        $batch->period_end->toDateString(),
            lwop_days:         0,
            tardiness_minutes: 0
        );

        return view('special-payroll.newly-hired-show', compact('batch', 'employee', 'result'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Newly Hired — Approve / Release
    //  POST /special-payroll/newly-hired/{id}/approve
    // ─────────────────────────────────────────────────────────────────────
    public function newHireApprove(Request $request, int $id)
    {
        $batch = SpecialPayrollBatch::where('type', 'newly_hired')->findOrFail($id);

        $old = $batch->status;

        if ($batch->status === 'draft') {
            $this->authorizeRole(['accountant']);
            $new    = 'approved';
            $action = 'Approved Newly Hired Payroll';
        } elseif ($batch->status === 'approved') {
            $this->authorizeRole(['ard', 'chief_admin_officer']);
            $new    = 'released';
            $action = 'Released Newly Hired Payroll';
        } else {
            return back()->with('error', 'This payroll record cannot be advanced further.');
        }

        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $batch->update([
            'status'      => $new,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks'     => $request->remarks ?? $batch->remarks,
        ]);

        PayrollAuditLog::create([
            'payroll_batch_id' => null,
            'user_id'          => Auth::id(),
            'action'           => $action . ': ' . $batch->title,
            'old_value'        => $old,
            'new_value'        => $new,
            'ip_address'       => $request->ip(),
        ]);

        $label = $new === 'approved' ? 'approved' : 'approved and released';

        return redirect()->route('special-payroll.newly-hired.show', $batch->id)
            ->with('success', "Payroll record {$label} successfully.");
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Newly Hired — Destroy (draft only)
    //  DELETE /special-payroll/newly-hired/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function newHireDestroy(int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $batch = SpecialPayrollBatch::where('type', 'newly_hired')
            ->where('status', 'draft')
            ->findOrFail($id);

        $batch->delete();

        return redirect()->route('special-payroll.newly-hired.index')
            ->with('success', 'Payroll record deleted.');
    }


    // ─────────────────────────────────────────────────────────────────────
//  Differential — Destroy (draft only)
//  DELETE /special-payroll/differential/{id}
// ─────────────────────────────────────────────────────────────────────
public function differentialDestroy(int $id)
{
    $this->authorizeRole(['payroll_officer', 'hrmo']);

    $batch = SpecialPayrollBatch::where('type', 'salary_differential')
        ->where('status', 'draft')
        ->findOrFail($id);

    $batch->delete();

    return redirect()->route('special-payroll.differential.index')
        ->with('success', 'Record deleted.');
}
    // ═════════════════════════════════════════════════════════════════════
    //  SALARY DIFFERENTIAL
    // ═════════════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────────────
    //  Differential — Index
    //  GET /special-payroll/differential
    // ─────────────────────────────────────────────────────────────────────
    public function differentialIndex(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = SpecialPayrollBatch::with('employee')
            ->where('type', 'salary_differential')
            ->orderByDesc('id');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches     = $query->paginate(20)->withQueryString();
        $currentYear = now()->year;

        return view('special-payroll.differential-index', compact('batches', 'currentYear'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Differential — Create Form
    //  GET /special-payroll/differential/create
    // ─────────────────────────────────────────────────────────────────────
    public function differentialCreate()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
->get(['id', 'last_name', 'first_name', 'middle_name',
       'position_title', 'basic_salary']);

        return view('special-payroll.differential-create', compact('employees'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Differential — Store
    //  POST /special-payroll/differential
    // ─────────────────────────────────────────────────────────────────────
    public function differentialStore(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo']);

        $request->validate([
            'employee_id'          => ['required', 'integer', 'exists:employees,id'],
            'effectivity_date_from'=> ['required', 'date'],
            'effectivity_date_to'  => ['required', 'date', 'after_or_equal:effectivity_date_from'],
            'old_salary'           => ['required', 'numeric', 'min:0'],
            'new_salary'           => ['required', 'numeric', 'gt:old_salary'],
            'remarks'              => ['nullable', 'string', 'max:1000'],
        ], [
            'new_salary.gt' => 'New salary must be greater than the old salary.',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        /** @var SalaryDifferentialService $service */
        $service = app(SalaryDifferentialService::class);

        $result = $service->compute(
            employee:              $employee,
            effectivity_date_from: $request->effectivity_date_from,
            effectivity_date_to:   $request->effectivity_date_to,
            old_salary:            (float) $request->old_salary,
            new_salary:            (float) $request->new_salary,
        );

        $from  = Carbon::parse($request->effectivity_date_from);
        $to    = Carbon::parse($request->effectivity_date_to);
        $title = 'Salary Differential — '
            . $employee->last_name . ', ' . $employee->first_name
            . ' (' . $from->format('M d, Y') . ' – ' . $to->format('M d, Y') . ')';

        $batch = SpecialPayrollBatch::create([
            'type'                => 'salary_differential',
            'title'               => $title,
            'year'                => $from->year,
            'month'               => $from->month,
            'effectivity_date'    => $request->effectivity_date_from,
            'period_start'        => $request->effectivity_date_from,
            'period_end'          => $request->effectivity_date_to,
            'employee_id'         => $employee->id,
            'old_basic_salary'    => $request->old_salary,
            'new_basic_salary'    => $request->new_salary,
            'differential_amount' => $result['differential'],
            'gross_amount'        => $result['total_earned'],
            'deductions_amount'   => $result['total_deductions'],
            'net_amount'          => $result['net_amount'],
            'status'              => 'draft',
            'remarks'             => $request->remarks,
        ]);

        PayrollAuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Created Salary Differential: ' . $employee->last_name . ', ' . $employee->first_name,
            'old_value'  => null,
            'new_value'  => 'draft',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('special-payroll.differential.show', $batch->id)
            ->with('success', "Salary differential created for {$employee->last_name}, {$employee->first_name}.");
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Differential — Show
    //  GET /special-payroll/differential/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function differentialShow(int $id)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $batch = SpecialPayrollBatch::with('employee', 'approver')
            ->where('type', 'salary_differential')
            ->findOrFail($id);

        $employee = $batch->employee;

        // Re-compute from stored inputs to get full per-month breakdown
        /** @var SalaryDifferentialService $service */
        $service = app(SalaryDifferentialService::class);

        $result = $service->compute(
            employee:              $employee,
            effectivity_date_from: $batch->period_start->toDateString(),
            effectivity_date_to:   $batch->period_end->toDateString(),
            old_salary:            (float) $batch->old_basic_salary,
            new_salary:            (float) $batch->new_basic_salary,
        );

        return view('special-payroll.differential-show', compact('batch', 'employee', 'result'));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Differential — Approve / Release
    //  POST /special-payroll/differential/{id}/approve
    // ─────────────────────────────────────────────────────────────────────
    public function differentialApprove(Request $request, int $id)
    {
        $batch = SpecialPayrollBatch::where('type', 'salary_differential')->findOrFail($id);

        $old = $batch->status;

        if ($batch->status === 'draft') {
            $this->authorizeRole(['accountant']);
            $new    = 'approved';
            $action = 'Approved Salary Differential';
        } elseif ($batch->status === 'approved') {
            $this->authorizeRole(['ard', 'chief_admin_officer']);
            $new    = 'released';
            $action = 'Released Salary Differential';
        } else {
            return back()->with('error', 'This payroll record cannot be advanced further.');
        }

        $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);

        $batch->update([
            'status'      => $new,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks'     => $request->remarks ?? $batch->remarks,
        ]);

        PayrollAuditLog::create([
            'payroll_batch_id' => null,
            'user_id'          => Auth::id(),
            'action'           => $action . ': ' . $batch->title,
            'old_value'        => $old,
            'new_value'        => $new,
            'ip_address'       => $request->ip(),
        ]);

        $label = $new === 'approved' ? 'approved' : 'approved and released';

        return redirect()->route('special-payroll.differential.show', $batch->id)
            ->with('success', "Salary differential record {$label} successfully.");
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
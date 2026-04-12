<?php

namespace App\Http\Controllers;

use App\Exports\GsisDetailedExport;
use App\Exports\GsisSummaryExport;
use App\Exports\TevRegisterExport;
use App\Models\Employee;
use App\Models\TevRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Itinerary of Travel (Appendix A)
    //  GET /reports/tev/{tevRequest}/itinerary
    // ─────────────────────────────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Itinerary of Travel (Appendix A)
    //  GET /reports/tev/{tevRequest}/itinerary
    // ─────────────────────────────────────────────────────────────────────────
    public function tevItinerary(int $tevRequest)
    {
        $this->authorizeRole(['hrmo', 'accountant', 'budget_officer', 'ard', 'cashier', 'chief_admin_officer']);
 
        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);
 
        return view('reports.tev-itinerary', compact('tev'));
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Certification of Travel Completed
    //  GET /reports/tev/{tevRequest}/travel-completed
    // ─────────────────────────────────────────────────────────────────────────
    public function tevTravelCompleted(int $tevRequest)
    {
        $this->authorizeRole(['hrmo', 'accountant', 'budget_officer', 'ard', 'cashier', 'chief_admin_officer']);
 
        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);
 
        return view('reports.tev-travel-completed', compact('tev'));
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Annex A: Expenses Not Requiring Receipts
    //  GET /reports/tev/{tevRequest}/annex-a
    // ─────────────────────────────────────────────────────────────────────────
    public function tevAnnexA(int $tevRequest)
    {
        $this->authorizeRole(['hrmo', 'accountant', 'budget_officer', 'ard', 'cashier', 'chief_admin_officer']);
 
        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);
 
        return view('reports.tev-annex-a', compact('tev'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Liquidation / Disbursement Voucher  (printable HTML page)
    //  GET /reports/tev/{tevRequest}/liquidation-dv
    // ─────────────────────────────────────────────────────────────────────────
    public function tevLiquidationDv(int $tevRequest)
    {
        $this->authorizeRole(['hrmo', 'accountant', 'budget_officer', 'ard', 'cashier', 'chief_admin_officer']);
 
        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
            'approvalLogs' => fn($q) => $q->with('user')->orderBy('performed_at'),
        ])->findOrFail($tevRequest);
 
        if ($tev->track !== 'cash_advance') {
            abort(404, 'Liquidation DV is only available for Cash Advance TEVs.');
        }
 
        if (!in_array($tev->status, ['liquidation_filed', 'liquidated'])) {
            abort(404, 'Liquidation has not been filed for this TEV yet.');
        }
 
        return view('reports.tev-liquidation-dv', compact('tev'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TEV Register (HTML)
    //  GET /reports/tev-register
    // ─────────────────────────────────────────────────────────────────────────
    public function tevRegister(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = TevRequest::with(['employee.division', 'officeOrder'])
            ->orderByDesc('travel_date_start');

        if ($request->filled('year')) {
            $query->whereYear('travel_date_start', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('travel_date_start', $request->month);
        }
        if ($request->filled('track')) {
            $query->where('track', $request->track);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $tevRequests = $query->paginate(30)->withQueryString();
        $grandTotal  = $query->getQuery()->clone()->sum('grand_total');
        $employees   = Employee::orderBy('last_name')->get(['id', 'last_name', 'first_name']);
        $currentYear = now()->year;
        $filters     = $request->only(['year', 'month', 'track', 'status', 'employee_id']);

        return view('reports.tev-register', compact(
            'tevRequests', 'grandTotal', 'employees', 'currentYear', 'filters'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TEV Register Excel Export
    //  GET /reports/tev-register/export
    // ─────────────────────────────────────────────────────────────────────────
    public function tevRegisterExport(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $filters = $request->only(['year', 'month', 'track', 'status', 'employee_id']);

        return Excel::download(
            new TevRegisterExport($filters),
            'TEV-Register-' . now()->format('Ymd') . '.xlsx'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Employee TEV History
    //  GET /employees/{employee}/tev-history
    // ─────────────────────────────────────────────────────────────────────────
    public function employeeTevHistory(Request $request, int $employee)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $emp = Employee::with('division')->findOrFail($employee);

        $tevRequests = TevRequest::with('officeOrder')
            ->where('employee_id', $emp->id)
            ->orderByDesc('travel_date_start')
            ->paginate(20)
            ->withQueryString();

        return view('employees.tev-history', compact('emp', 'tevRequests'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Reports Index
    //  GET /reports
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);
        return view('reports.index');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  GSIS — Filter / Preview page
    //  GET /reports/gsis
    // ─────────────────────────────────────────────────────────────────────────
    public function gsisIndex(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $year   = (int) $request->get('year',   now()->year);
        $month  = (int) $request->get('month',  now()->month);
        $cutoff = $request->get('cutoff', 'both');

        $summaryExport = new GsisSummaryExport($year, $month, $cutoff);
        $totals        = $summaryExport->getTotals();
        $employeeCount = $summaryExport->getEmployeeCount();
        $grandTotal    = array_sum($totals);

        // Label map: deduction_type code => human-readable name for preview table.
        // Codes must match DeductionTypeSeeder exactly.
        $labelMap = [
            'GSIS_LIFE_RETIREMENT' => 'Life/Retirement Premium Personal Share',
            'GSIS_EMERGENCY'       => 'Emergency Loan',
            'GSIS_EDUC'            => 'Educational Assistance Loan',
            'GSIS_MPL_LITE'        => 'Multi-Purpose Loan Lite (MPL Lite)',
            'GSIS_CONSO'           => 'Consolidated Loan',
            'GSIS_HELP'            => 'Home Emergency Loan',
            'GSIS_GFAL'            => 'GSIS Financial Assistance Program (GFAL)',
            'GSIS_MPL'             => 'Multi-Purpose Loan (MPL)',
            'GSIS_CPL'             => 'GSIS Computer Loan (CPL)',
            'GSIS_POLICY'          => 'Policy Loan - optional',
            'GSIS_REAL_ESTATE'     => 'Real Estate Loan',
        ];

        $currentYear = now()->year;
        $months = [
            1 => 'January',   2 => 'February',  3 => 'March',
            4 => 'April',     5 => 'May',        6 => 'June',
            7 => 'July',      8 => 'August',     9 => 'September',
            10 => 'October',  11 => 'November',  12 => 'December',
        ];

        return view('reports.gsis', compact(
            'year', 'month', 'cutoff',
            'totals', 'labelMap', 'employeeCount', 'grandTotal',
            'currentYear', 'months'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  GSIS — Summary Excel Download
    //  GET /reports/gsis-summary
    // ─────────────────────────────────────────────────────────────────────────
    public function gsisSummary(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $request->validate([
            'year'   => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'  => ['required', 'integer', 'min:1',    'max:12'],
            'cutoff' => ['nullable', 'in:1st,2nd,both'],
        ]);

        $year   = (int) $request->year;
        $month  = (int) $request->month;
        $cutoff = $request->get('cutoff', 'both');

        $filename = sprintf('GSIS-Summary-%04d-%02d-%s.xlsx', $year, $month, $cutoff);

        return Excel::download(new GsisSummaryExport($year, $month, $cutoff), $filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  GSIS — Detailed Excel Download
    //  GET /reports/gsis-detailed
    // ─────────────────────────────────────────────────────────────────────────
    public function gsisDetailed(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $request->validate([
            'year'   => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'  => ['required', 'integer', 'min:1',    'max:12'],
            'cutoff' => ['nullable', 'in:1st,2nd,both'],
        ]);

        $year   = (int) $request->year;
        $month  = (int) $request->month;
        $cutoff = $request->get('cutoff', 'both');

        $filename = sprintf('GSIS-Detailed-%04d-%02d-%s.xlsx', $year, $month, $cutoff);

        return Excel::download(new GsisDetailedExport($year, $month, $cutoff), $filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Stubs for Phase 3A Steps 2–4 (routes already registered in web.php)
    //  Each will be implemented in the next phase sessions.
    // ─────────────────────────────────────────────────────────────────────────

    public function payrollRegister(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);
        // TODO: Phase 3A Step 4
        abort(501, 'Not yet implemented.');
    }

    public function payslip(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);
        // TODO: Phase 3A Step 4
        abort(501, 'Not yet implemented.');
    }

    public function hdmfP1(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        // TODO: Phase 3A Step 2
        abort(501, 'Not yet implemented.');
    }

    public function hdmfP2(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function hdmfMpl(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function hdmfCal(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function hdmfHousing(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function caressUnion(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        // TODO: Phase 3A Step 3
        abort(501, 'Not yet implemented.');
    }

    public function caressMortuary(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function lbpLoan(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function mass(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function providentFund(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    public function btrRefund(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);
        abort(501, 'Not yet implemented.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private helper
    // ─────────────────────────────────────────────────────────────────────────
    private function authorizeRole(array $roles): void
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Exports\GsisDetailedExport;
use App\Exports\GsisSummaryExport;
use App\Exports\HdmfRemittanceExport;
use App\Exports\HdmfP1Export;
use App\Exports\HdmfP2Export;
use App\Exports\HdmfMplExport;
use App\Exports\HdmfCalExport;
use App\Exports\HdmfHousingExport;
use App\Exports\TevRegisterExport;
use App\Exports\LbpLoanExport;
use App\Exports\CaressUnionDuesExport;
use App\Exports\CaressMortuaryExport;
use App\Exports\MassExport;
use App\Exports\ProvidentFundExport;
use App\Exports\BtrRefundExport;
use App\Models\Employee;
use App\Models\TevRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // ----------------------------------------------------------------
    // TEV printable documents
    // ----------------------------------------------------------------

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

    /**
     * Liquidation Disbursement Voucher — only available for cash_advance TEVs
     * that have already had liquidation filed. Reimbursement-track TEVs skip
     * this step entirely and will 404 here.
     */
    public function tevLiquidationDv(int $tevRequest)
    {
        $this->authorizeRole(['hrmo', 'accountant', 'budget_officer', 'ard', 'cashier', 'chief_admin_officer']);

        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
            'approvalLogs' => fn ($q) => $q->with('user')->orderBy('performed_at'),
        ])->findOrFail($tevRequest);

        if ($tev->track !== 'cash_advance') {
            abort(404, 'Liquidation DV is only available for Cash Advance TEVs.');
        }

        if (! in_array($tev->status, ['liquidation_filed', 'liquidated'])) {
            abort(404, 'Liquidation has not been filed for this TEV yet.');
        }

        return view('reports.tev-liquidation-dv', compact('tev'));
    }

    // ----------------------------------------------------------------
    // TEV register
    // ----------------------------------------------------------------

    public function tevRegister(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $query = TevRequest::with(['employee.division', 'officeOrder'])
            ->orderByDesc('travel_date_start');

        if ($request->filled('year'))        $query->whereYear('travel_date_start',  $request->year);
        if ($request->filled('month'))       $query->whereMonth('travel_date_start', $request->month);
        if ($request->filled('track'))       $query->where('track',       $request->track);
        if ($request->filled('status'))      $query->where('status',      $request->status);
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);

        $tevRequests = $query->paginate(30)->withQueryString();
        $grandTotal  = $query->getQuery()->clone()->sum('grand_total');
        $employees   = Employee::orderBy('last_name')->get(['id', 'last_name', 'first_name']);
        $currentYear = now()->year;
        $filters     = $request->only(['year', 'month', 'track', 'status', 'employee_id']);

        return view('reports.tev-register', compact(
            'tevRequests', 'grandTotal', 'employees', 'currentYear', 'filters'
        ));
    }

    public function tevRegisterExport(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        return Excel::download(
            new TevRegisterExport($request->only(['year', 'month', 'track', 'status', 'employee_id'])),
            'TEV-Register-' . now()->format('Ymd') . '.xlsx'
        );
    }

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

    // ----------------------------------------------------------------
    // Reports index
    // ----------------------------------------------------------------

    public function index()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        return view('reports.index');
    }

    // ----------------------------------------------------------------
    // GSIS
    // ----------------------------------------------------------------

    public function gsisIndex(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        $summaryExport = new GsisSummaryExport($year, $month, $cutoff);
        $totals        = $summaryExport->getTotals();
        $employeeCount = $summaryExport->getEmployeeCount();
        $grandTotal    = array_sum($totals);

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
        $months      = $this->monthNames();

        return view('reports.gsis', compact(
            'year', 'month', 'cutoff',
            'totals', 'labelMap', 'employeeCount', 'grandTotal',
            'currentYear', 'months'
        ));
    }

    public function gsisSummary(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $request->validate([
            'year'   => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'  => ['required', 'integer', 'min:1',    'max:12'],
            'cutoff' => ['nullable', 'in:1st,2nd,both'],
        ]);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        return Excel::download(
            new GsisSummaryExport($year, $month, $cutoff),
            sprintf('GSIS-Summary-%04d-%02d-%s.xlsx', $year, $month, $cutoff)
        );
    }

    public function gsisDetailed(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $request->validate([
            'year'   => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'  => ['required', 'integer', 'min:1',    'max:12'],
            'cutoff' => ['nullable', 'in:1st,2nd,both'],
        ]);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        return Excel::download(
            new GsisDetailedExport($year, $month, $cutoff),
            sprintf('GSIS-Detailed-%04d-%02d-%s.xlsx', $year, $month, $cutoff)
        );
    }

    // ----------------------------------------------------------------
    // HDMF / Pag-IBIG
    // ----------------------------------------------------------------

    public function hdmfIndex(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        $p1      = new HdmfP1Export($year, $month, $cutoff);
        $p2      = new HdmfP2Export($year, $month, $cutoff);
        $mpl     = new HdmfMplExport($year, $month, $cutoff);
        $cal     = new HdmfCalExport($year, $month, $cutoff);
        $housing = new HdmfHousingExport($year, $month, $cutoff);

        $sheets = [
            ['label' => 'Pag-IBIG I (P1)',           'program' => 'F1',  'count' => $p1->getCount(),      'total' => $p1->getTotal()],
            ['label' => 'Modified Pag-IBIG II (P2)',  'program' => 'M2',  'count' => $p2->getCount(),      'total' => $p2->getTotal()],
            ['label' => 'Multi-Purpose Loan (MPL)',   'program' => 'MPL', 'count' => $mpl->getCount(),     'total' => $mpl->getTotal()],
            ['label' => 'Calamity Loan (CAL)',        'program' => 'CAL', 'count' => $cal->getCount(),     'total' => $cal->getTotal()],
            ['label' => 'Housing Loan (HL)',           'program' => 'HL',  'count' => $housing->getCount(), 'total' => $housing->getTotal()],
        ];

        $grandTotal = array_sum(array_column($sheets, 'total'));

        // P1 covers the broadest set of contributors — used as the headline employee count
        $employeeCount = $p1->getCount();

        $currentYear = now()->year;
        $months      = $this->monthNames();

        return view('reports.hdmf', compact(
            'year', 'month', 'cutoff',
            'sheets', 'grandTotal', 'employeeCount',
            'currentYear', 'months'
        ));
    }

    public function hdmf(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        $request->validate([
            'year'   => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'  => ['required', 'integer', 'min:1',    'max:12'],
            'cutoff' => ['nullable', 'in:1st,2nd,both'],
        ]);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        return Excel::download(
            new HdmfRemittanceExport($year, $month, $cutoff),
            sprintf('HDMF-Remittance-%04d-%02d-%s.xlsx', $year, $month, $cutoff)
        );
    }

    // ----------------------------------------------------------------
    // Remittances hub and per-deduction report/download methods
    //
    // Each method below follows the same pattern:
    //   - If ?download is present, stream an Excel file immediately.
    //   - Otherwise, query the deduction rows and render the preview view.
    // The deduction code used for each method is noted inline.
    // ----------------------------------------------------------------

    public function remittancesHub()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        return view('reports.remittances', [
            'year'        => $year,
            'month'       => $month,
            'cutoff'      => $cutoff,
            'currentYear' => now()->year,
            'months'      => $this->monthNames(),
        ]);
    }

    /**
     * PhilHealth contributions CSV.
     *
     * PhilHealth does not accept a system-generated remittance file — the
     * official PDF billing must be generated from the PHIC Employer Portal.
     * This CSV is intended for manual reconciliation or upload preparation only.
     */
    public function phicCsv()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();
        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $rows = $this->deductionRows('PHIC', $year, $month, $cutoff)
            ->map(function ($ded) {
                $emp = $ded->payrollEntry->employee;
                return [
                    strtoupper($emp->last_name . ', ' . $emp->first_name),
                    $emp->philhealth_no ?? '',
                    number_format($emp->semi_monthly_gross * 2, 2),
                    number_format($ded->amount, 2),
                ];
            })
            ->sortBy(fn ($r) => $r[0])
            ->values();

        $filename = "PHIC_{$year}_{$month}_contributions.csv";
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $monthName, $year) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['PhilHealth Contributions — ' . $monthName . ' ' . $year]);
            fputcsv($out, ['Note: Generate PDF Billing and PHIC Remittance from the PHIC Employer Portal.']);
            fputcsv($out, []);
            fputcsv($out, ['NAME', 'PHILHEALTH NO.', 'BASIC MONTHLY SALARY', 'EE SHARE']);
            foreach ($rows as $row) fputcsv($out, $row);
            fputcsv($out, []);
            fputcsv($out, ['TOTAL', '', '', number_format($rows->sum(fn ($r) => (float) str_replace(',', '', $r[3])), 2)]);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * SSS Voluntary contributions CSV.
     * Remittance itself is processed via the SSS Employer Portal.
     */
    public function sssVoluntary()
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();
        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $rows = $this->deductionRows('SSS', $year, $month, $cutoff)
            ->map(function ($ded) {
                $emp = $ded->payrollEntry->employee;
                return [
                    strtoupper($emp->last_name . ', ' . $emp->first_name),
                    $emp->sss_no ?? '',
                    number_format($ded->amount, 2),
                ];
            })
            ->sortBy(fn ($r) => $r[0])
            ->values();

        $filename = "SSS_Voluntary_{$year}_{$month}.csv";
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $monthName, $year) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['SSS Voluntary Contributions — ' . $monthName . ' ' . $year]);
            fputcsv($out, ['Note: Generate PDF Billing and SSS Remittance via SSS Employer Portal.']);
            fputcsv($out, []);
            fputcsv($out, ['NAME', 'SSS NO.', 'AMOUNT']);
            foreach ($rows as $row) fputcsv($out, $row);
            fputcsv($out, []);
            fputcsv($out, ['TOTAL', '', number_format($rows->sum(fn ($r) => (float) str_replace(',', '', $r[2])), 2)]);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Deduction code: LBP_LOAN
    public function lbpLoan(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        if ($request->has('download')) {
            return Excel::download(
                new LbpLoanExport($year, $month, $cutoff),
                'LBP_Loan_' . $year . '_' . date('F', mktime(0, 0, 0, $month, 1)) . '.xlsx'
            );
        }

        $rows = $this->deductionRows('LBP_LOAN', $year, $month, $cutoff);

        return $this->remittancePreview($year, $month, $cutoff, 'lbp', $rows);
    }

    // Deduction code: CARESS_UNION
    public function caressUnion(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        if ($request->has('download')) {
            return Excel::download(
                new CaressUnionDuesExport($year, $month, $cutoff),
                'CARESS_UnionDues_' . $year . '_' . date('F', mktime(0, 0, 0, $month, 1)) . '.xlsx'
            );
        }

        $rows = $this->deductionRows('CARESS_UNION', $year, $month, $cutoff);

        return $this->remittancePreview($year, $month, $cutoff, 'caress_union', $rows);
    }

    // Deduction code: CARESS_MORTUARY
    public function caressMortuary(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        if ($request->has('download')) {
            return Excel::download(
                new CaressMortuaryExport($year, $month, $cutoff),
                'CARESS_Mortuary_' . $year . '_' . date('F', mktime(0, 0, 0, $month, 1)) . '.xlsx'
            );
        }

        $rows = $this->deductionRows('CARESS_MORTUARY', $year, $month, $cutoff);

        return $this->remittancePreview($year, $month, $cutoff, 'caress_mortuary', $rows);
    }

    // Deduction code: MASS
    public function mass(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        if ($request->has('download')) {
            return Excel::download(
                new MassExport($year, $month, $cutoff),
                'MASS_' . $year . '_' . date('F', mktime(0, 0, 0, $month, 1)) . '.xlsx'
            );
        }

        $rows = $this->deductionRows('MASS', $year, $month, $cutoff);

        return $this->remittancePreview($year, $month, $cutoff, 'mass', $rows);
    }

    // Deduction code: PROVIDENT_FUND
    public function providentFund(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        if ($request->has('download')) {
            return Excel::download(
                new ProvidentFundExport($year, $month, $cutoff),
                'ProvidentFund_' . $year . '_' . date('F', mktime(0, 0, 0, $month, 1)) . '.xlsx'
            );
        }

        $rows = $this->deductionRows('PROVIDENT_FUND', $year, $month, $cutoff);

        return $this->remittancePreview($year, $month, $cutoff, 'provident_fund', $rows);
    }

    // Deduction codes: WHT + REFUND_VARIOUS (withheld tax and refunds remitted together to BTR)
    public function btrRefund(Request $request)
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant']);

        [$year, $month, $cutoff] = $this->remittanceFilters();

        if ($request->has('download')) {
            return Excel::download(
                new BtrRefundExport($year, $month, $cutoff),
                'BTR_Refund_' . $year . '_' . date('F', mktime(0, 0, 0, $month, 1)) . '.xlsx'
            );
        }

        // BTR covers two deduction types — query them together
        $batches         = $this->batchIdsForPeriod($year, $month, $cutoff);
        $deductionTypes  = \App\Models\DeductionType::whereIn('code', ['WHT', 'REFUND_VARIOUS'])->pluck('id');

        $rows = \App\Models\PayrollDeduction::with(['payrollEntry.employee', 'deductionType'])
            ->whereIn('payroll_entry_id', fn ($q) => $q->select('id')->from('payroll_entries')->whereIn('payroll_batch_id', $batches))
            ->whereIn('deduction_type_id', $deductionTypes)
            ->where('amount', '>', 0)
            ->get();

        return $this->remittancePreview($year, $month, $cutoff, 'btr', $rows);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function authorizeRole(array $roles): void
    {
        if (! Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }

    private function monthNames(): array
    {
        return [
            1 => 'January',  2 => 'February',  3 => 'March',
            4 => 'April',    5 => 'May',        6 => 'June',
            7 => 'July',     8 => 'August',     9 => 'September',
            10 => 'October', 11 => 'November',  12 => 'December',
        ];
    }

    /**
     * Resolve year/month/cutoff from the current request.
     * All remittance methods share the same three filter params.
     */
    private function remittanceFilters(): array
    {
        return [
            (int) request('year',   now()->year),
            (int) request('month',  now()->month),
            request('cutoff', 'both'),
        ];
    }

    /**
     * Resolve payroll batch IDs for a given period and cut-off.
     * Extracted to avoid repeating the same query across every remittance method.
     */
    private function batchIdsForPeriod(int $year, int $month, string $cutoff)
    {
        return \App\Models\PayrollBatch::query()
            ->whereYear('period_start',  $year)
            ->whereMonth('period_start', $month)
            ->when($cutoff === '1st', fn ($q) => $q->whereDay('period_start', '<=', 15))
            ->when($cutoff === '2nd', fn ($q) => $q->whereDay('period_start', '>',  15))
            ->pluck('id');
    }

    /**
     * Fetch PayrollDeduction rows for a single deduction code and period.
     * Used by every remittance preview/download method except btrRefund,
     * which spans two codes and queries directly.
     */
    private function deductionRows(string $code, int $year, int $month, string $cutoff)
    {
        $batches         = $this->batchIdsForPeriod($year, $month, $cutoff);
        $deductionTypeId = \App\Models\DeductionType::where('code', $code)->value('id');

        return \App\Models\PayrollDeduction::with('payrollEntry.employee')
            ->whereIn('payroll_entry_id', fn ($q) => $q->select('id')->from('payroll_entries')->whereIn('payroll_batch_id', $batches))
            ->where('deduction_type_id', $deductionTypeId)
            ->where('amount', '>', 0)
            ->get();
    }

    /**
     * Render the shared remittances preview view with per-report row data.
     * All single-deduction remittance methods delegate their preview response here.
     */
    private function remittancePreview(int $year, int $month, string $cutoff, string $activeReport, $rows)
    {
        return view('reports.remittances', [
            'year'          => $year,
            'month'         => $month,
            'cutoff'        => $cutoff,
            'currentYear'   => now()->year,
            'months'        => $this->monthNames(),
            'activeReport'  => $activeReport,
            'reportRows'    => $rows,
            'grandTotal'    => $rows->sum('amount'),
            'employeeCount' => $rows->count(),
        ]);
    }
}
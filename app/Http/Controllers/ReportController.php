<?php

namespace App\Http\Controllers;

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
    public function tevItinerary(int $tevRequest): \Illuminate\Http\Response
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $tev = TevRequest::with([
            'itineraryLines',
            'employee',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);

        $pdf = Pdf::loadView('reports.tev-itinerary', compact('tev'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('TEV-' . $tev->tev_no . '-itinerary.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Certification of Travel Completed
    //  GET /reports/tev/{tevRequest}/travel-completed
    // ─────────────────────────────────────────────────────────────────────────
    public function tevTravelCompleted(int $tevRequest): \Illuminate\Http\Response
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $tev = TevRequest::with([
            'itineraryLines',
            'employee',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);

        $pdf = Pdf::loadView('reports.tev-travel-completed', compact('tev'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('TEV-' . $tev->tev_no . '-travel-completed.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Annex A: Expenses Not Requiring Receipts
    //  GET /reports/tev/{tevRequest}/annex-a
    // ─────────────────────────────────────────────────────────────────────────
    public function tevAnnexA(int $tevRequest): \Illuminate\Http\Response
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);

        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);

        $pdf = Pdf::loadView('reports.tev-annex-a', compact('tev'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('TEV-' . $tev->tev_no . '-annex-a.pdf');
    }


    // ─────────────────────────────────────────────────────────────────────────
    //  TEV — Liquidation / Disbursement Voucher
    //  GET /reports/tev/{tevRequest}/liquidation-dv
    // ─────────────────────────────────────────────────────────────────────────
    public function tevLiquidationDv(int $tevRequest): \Illuminate\Http\Response
    {
        $this->authorizeRole(['payroll_officer', 'hrmo', 'accountant', 'ard', 'cashier']);
 
        $tev = TevRequest::with([
            'itineraryLines',
            'employee.division',
            'officeOrder',
            'certification',
        ])->findOrFail($tevRequest);
 
        if ($tev->track !== 'cash_advance') {
            abort(404, 'Liquidation DV is only available for Cash Advance TEVs.');
        }
 
        if (!in_array($tev->status, ['liquidation_filed', 'liquidated'])) {
            abort(404, 'Liquidation has not been filed for this TEV yet.');
        }
 
        $pdf = Pdf::loadView('reports.tev-liquidation-dv', compact('tev'))
            ->setPaper('a4', 'portrait');
 
        return $pdf->stream('TEV-' . $tev->tev_no . '-liquidation-dv.pdf');
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

        $tevRequests  = $query->paginate(30)->withQueryString();
        $grandTotal   = $query->getQuery()->clone()->sum('grand_total');
        $employees    = Employee::orderBy('last_name')->get(['id', 'last_name', 'first_name']);
        $currentYear  = now()->year;
        $filters      = $request->only(['year', 'month', 'track', 'status', 'employee_id']);

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
    //  Private helper
    // ─────────────────────────────────────────────────────────────────────────
    private function authorizeRole(array $roles): void
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            abort(403);
        }
    }
}
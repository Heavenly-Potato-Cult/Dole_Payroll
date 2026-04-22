<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\TevRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Main dashboard view.
     *
     * The dashboard is role-aware: each role sees pending counts scoped
     * only to the queue they are responsible for acting on. Shared stats
     * (employee count, recent activity, charts) are visible to everyone.
     *
     * Roles and their queues:
     *   payroll_officer      → draft/computed payroll batches
     *   hrmo                 → same as above + CA-track TEVs needing liquidation
     *   accountant           → payroll pending certification + submitted TEVs
     *   ard/chief_admin      → payroll pending RD approval + accountant-certified TEVs
     *   cashier              → RD-approved TEVs to release + filed liquidations to process
     *   budget_officer       → submitted TEVs (read-only reference, no action)
     */
    public function index()
    {
        $user = Auth::user();

        // ----------------------------------------------------------------
        // Shared context
        // Displayed on all dashboard variants regardless of role.
        // ----------------------------------------------------------------

        $totalEmployees = Employee::where('status', 'active')->count();
        $currentCutoff  = (now()->day <= 15) ? '1st' : '2nd';
        $currentMonth   = now()->format('F Y');



        // ----------------------------------------------------------------
        // Role-scoped pending counts
        //
        // Counts are intentionally narrow: each variable reflects only
        // what the current user can act on, not the full system state.
        // This drives the "Pending Approvals" badge on the stat card.
        // ----------------------------------------------------------------

        $pendingPayroll     = 0;
        $pendingTev         = 0;
        $pendingLiquidation = 0; // cashier-only: TEVs with filed liquidations to process



        // Total TEVs filed this month - role-neutral, used across stat cards
        $tevThisMonth = TevRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($user->hasRole('payroll_officer')) {
            $pendingPayroll = PayrollBatch::whereIn('status', ['draft', 'computed'])->count();

        } elseif ($user->hasAnyRole(['hrmo'])) {
            $pendingPayroll = PayrollBatch::whereIn('status', ['draft', 'computed'])->count();

            // Only cash_advance TEVs land back on HRMO after cashier releases them.
            // Reimbursement-track TEVs skip this step entirely.
            $pendingTev = TevRequest::where('status', 'cashier_released')
                            ->where('track', 'cash_advance')
                            ->count();

        } elseif ($user->hasRole('accountant')) {
            $pendingPayroll = PayrollBatch::where('status', 'pending_accountant')->count();
            $pendingTev     = TevRequest::where('status', 'submitted')->count();

        } elseif ($user->hasAnyRole(['ard', 'chief_admin_officer'])) {
            $pendingPayroll = PayrollBatch::where('status', 'pending_rd')->count();
            $pendingTev     = TevRequest::where('status', 'accountant_certified')->count();

        } elseif ($user->hasRole('cashier')) {
            $pendingTev         = TevRequest::where('status', 'rd_approved')->count();
            $pendingLiquidation = TevRequest::where('status', 'liquidation_filed')->count();

        } elseif ($user->hasRole('budget_officer')) {
            // Budget officer has no approval action — this count is for monitoring only
            $pendingTev = TevRequest::where('status', 'submitted')->count();
        }



        // Single badge total shown on the dashboard stat card header
        $pendingApprovals = $pendingPayroll + $pendingTev + $pendingLiquidation;


        // ----------------------------------------------------------------
        // Recent activity feeds
        // Latest 5 records for the dashboard tables. Role-based visibility
        // is handled in the Blade view, not here.
        // ----------------------------------------------------------------

        $recentPayroll = PayrollBatch::with('creator')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentTev = TevRequest::with(['employee', 'officeOrder'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();



        // ----------------------------------------------------------------
        // Chart datasets
        // Pre-aggregated for the dashboard charts. Keeping this in the
        // controller avoids raw queries leaking into Blade templates.
        // ----------------------------------------------------------------

        // Payroll pipeline distribution - ordered to match the workflow stages
        $statusOrder = ['draft', 'computed', 'pending_accountant', 'pending_rd', 'released', 'locked'];
        $rawCounts   = PayrollBatch::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Fill in zero for any status not yet present so the chart always
        // renders a complete pipeline, even on a fresh or sparse dataset
        $payrollStatusData = [];
        foreach ($statusOrder as $s) {
            $payrollStatusData[$s] = $rawCounts[$s] ?? 0;
        }

        // TEV breakdown by track (cash_advance vs reimbursement) and by status
        $tevByTrack = TevRequest::selectRaw('track, count(*) as total')
            ->groupBy('track')
            ->pluck('total', 'track')
            ->toArray();

        $tevByStatus = TevRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();


            
        return view('dashboard.index', compact(
            'totalEmployees',
            'currentCutoff',
            'currentMonth',
            'pendingApprovals',
            'pendingPayroll',
            'pendingTev',
            'pendingLiquidation',
            'tevThisMonth',
            'recentPayroll',
            'recentTev',
            'payrollStatusData',
            'tevByTrack',
            'tevByStatus',
        ));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\TevRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── Shared base stats ──────────────────────────────────────────
        $totalEmployees = Employee::where('status', 'active')->count();
        $currentCutoff  = (now()->day <= 15) ? '1st' : '2nd';
        $currentMonth   = now()->format('F Y');

        // ── Role-specific pending counts ───────────────────────────────
        //
        //  Each role sees ONLY the queue it can act on.
        //  Variables:
        //    $pendingPayroll     — payroll batches awaiting THIS role's action
        //    $pendingTev         — TEV requests awaiting THIS role's action
        //    $pendingLiquidation — liquidations awaiting THIS role (cashier only)
        //    $pendingApprovals   — combined badge shown on stat card
        //
        $pendingPayroll     = 0;
        $pendingTev         = 0;
        $pendingLiquidation = 0;

        // TEV counts by status (needed for stat cards on multiple roles)
        $tevThisMonth = TevRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($user->hasRole('payroll_officer')) {
            // Payroll Officer: sees draft+computed regular payroll batches they can act on
            $pendingPayroll = PayrollBatch::whereIn('status', ['draft', 'computed'])->count();
            // No TEV access at all

        } elseif ($user->hasAnyRole(['hrmo'])) {
            // HRMO: owns draft/computed payroll batches + all TEV statuses for info
            $pendingPayroll = PayrollBatch::whereIn('status', ['draft', 'computed'])->count();
            // TEV: HRMO can see TEVs needing liquidation (filed by them)
            $pendingTev     = TevRequest::where('status', 'cashier_released')
                                ->where('track', 'cash_advance')
                                ->count(); // CA TEVs awaiting HRMO to file liquidation

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
            // Budget officer monitors TEV submissions for reference
            $pendingTev = TevRequest::where('status', 'submitted')->count();
        }

        $pendingApprovals = $pendingPayroll + $pendingTev + $pendingLiquidation;

        // ── Recent lists ───────────────────────────────────────────────
        $recentPayroll = PayrollBatch::with('creator')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentTev = TevRequest::with(['employee', 'officeOrder'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // ── Payroll status distribution chart ─────────────────────────
        $statusOrder = ['draft', 'computed', 'pending_accountant', 'pending_rd', 'released', 'locked'];
        $rawCounts   = PayrollBatch::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $payrollStatusData = [];
        foreach ($statusOrder as $s) {
            $payrollStatusData[$s] = $rawCounts[$s] ?? 0;
        }

        // ── TEV track breakdown (for HRMO/accountant insight) ─────────
        $tevByTrack = TevRequest::selectRaw('track, count(*) as total')
            ->groupBy('track')
            ->pluck('total', 'track')
            ->toArray();

        // ── TEV status breakdown (for cashier/ard summary) ────────────
        $tevByStatus = TevRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('dashboard.index', compact(
            'totalEmployees',
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
            'currentCutoff',
            'currentMonth'
        ));
    }
}
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
        $roles = $user->getRoleNames()->toArray();

        // ── Total active employees ────────────────────────────────────
        $totalEmployees = Employee::where('status', 'active')->count();

        // ── Pending approvals (role-aware) ────────────────────────────
        $pendingApprovals = 0;

        if ($user->hasAnyRole(['payroll_officer', 'hrmo'])) {
            $pendingApprovals = PayrollBatch::whereIn('status', ['draft', 'computed'])->count();

        } elseif ($user->hasRole('accountant')) {
            $pendingApprovals =
                PayrollBatch::where('status', 'pending_accountant')->count()
                + TevRequest::where('status', 'hr_approved')->count();

        } elseif ($user->hasAnyRole(['ard', 'chief_admin_officer'])) {
            $pendingApprovals =
                PayrollBatch::where('status', 'pending_rd')->count()
                + TevRequest::where('status', 'accountant_certified')->count();

        } elseif ($user->hasRole('cashier')) {
            $pendingApprovals = TevRequest::where('status', 'rd_approved')->count();

        } elseif ($user->hasRole('budget_officer')) {
            $pendingApprovals = TevRequest::where('status', 'submitted')->count();
        }

        // ── TEV this month ────────────────────────────────────────────
        $tevThisMonth = TevRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ── Recent payroll batches ─────────────────────────────────────
        $recentPayroll = PayrollBatch::with('creator')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // ── Recent TEV requests ────────────────────────────────────────
        $recentTev = TevRequest::with(['employee', 'officeOrder'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // ── Payroll status distribution (for chart) ────────────────────
        $statusOrder = ['draft', 'computed', 'pending_accountant', 'pending_rd', 'released', 'locked'];

        $rawCounts = PayrollBatch::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $payrollStatusData = [];
        foreach ($statusOrder as $s) {
            $payrollStatusData[$s] = $rawCounts[$s] ?? 0;
        }

        // ── Cut-off helpers ────────────────────────────────────────────
        $currentCutoff = (now()->day <= 15) ? '1st' : '2nd';
        $currentMonth  = now()->format('F Y');

        return view('dashboard.index', compact(
            'totalEmployees',
            'pendingApprovals',
            'tevThisMonth',
            'recentPayroll',
            'recentTev',
            'payrollStatusData',
            'currentCutoff',
            'currentMonth'
        ));
    }
}
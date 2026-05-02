@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Welcome to DOLE RO9 Systems</h1>
        <p>Select the system you want to access</p>
    </div>
</div>

{{-- ── System Cards ─────────────────────────────────────────────── --}}
<div class="system-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; max-width: 800px; margin: 0 auto;">

    {{-- Payroll System Card --}}
    @if ($canAccessPayroll)
    <a href="{{ route('payroll.dashboard.main') }}" class="system-card" style="display: block; text-decoration: none; color: inherit;">
        <div class="card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s;">
            <div class="card-body" style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 48px; margin-bottom: 20px; color: var(--navy, #1e3a5f);">💰</div>
                <h3 style="margin-bottom: 12px; color: var(--navy, #1e3a5f);">Payroll System</h3>
                <p style="color: var(--text-mid); margin-bottom: 20px;">
                    Manage payroll batches, employee payslips, and compensation processing
                </p>
                <div style="font-size: 14px; color: var(--text-light);">
                    Semi-monthly payroll • Deduction management • Payslip generation
                </div>
            </div>
        </div>
    </a>
    @endif

    {{-- TEV System Card --}}
    @if ($canAccessTev)
    <a href="{{ route('tev.dashboard') }}" class="system-card" style="display: block; text-decoration: none; color: inherit;">
        <div class="card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s;">
            <div class="card-body" style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 48px; margin-bottom: 20px; color: var(--navy, #1e3a5f);">✈️</div>
                <h3 style="margin-bottom: 12px; color: var(--navy, #1e3a5f);">Travel Expense Voucher</h3>
                <p style="color: var(--text-mid); margin-bottom: 20px;">
                    Manage travel requests, reimbursements, and expense vouchers
                </p>
                <div style="font-size: 14px; color: var(--text-light);">
                    Travel requests • Office orders • Expense reimbursements
                </div>
            </div>
        </div>
    </a>
    @endif

</div>

@endsection

@section('styles')
<style>
.system-card:hover .card {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.system-card .card {
    border: 2px solid var(--border);
    border-radius: var(--radius, 8px);
    background: white;
}

.system-card .card-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.system-card h3 {
    font-size: 1.5rem;
    font-weight: 700;
}

.system-card p {
    line-height: 1.6;
}

@media (max-width: 768px) {
    .system-cards {
        grid-template-columns: 1fr;
        max-width: 100%;
    }
    
    .system-card .card-body {
        padding: 30px 20px;
    }
}
</style>
@endsection

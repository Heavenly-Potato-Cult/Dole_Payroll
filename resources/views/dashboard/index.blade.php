@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
        <p>{{ now()->format('l, F j, Y') }} &mdash; DOLE Regional Office IX, Zamboanga City</p>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Employees</div>
        <div class="stat-value">—</div>
        <div class="stat-sub">Regular plantilla items</div>
    </div>

    <div class="stat-card gold">
        <div class="stat-label">Current Cut-off</div>
        <div class="stat-value">
            {{ now()->day <= 15 ? '1st' : '2nd' }}
        </div>
        <div class="stat-sub">
            {{ now()->format('F Y') }}
            &mdash; {{ now()->day <= 15 ? '1–15' : '16–'.now()->daysInMonth }}
        </div>
    </div>

    <div class="stat-card red">
        <div class="stat-label">Pending Approvals</div>
        <div class="stat-value">—</div>
        <div class="stat-sub">Payroll &amp; TEV items</div>
    </div>

    <div class="stat-card green">
        <div class="stat-label">TEV Requests</div>
        <div class="stat-value">—</div>
        <div class="stat-sub">Active this month</div>
    </div>
</div>

{{-- Quick Access --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">

    {{-- Payroll Quick Access --}}
    @role('payroll_officer|hrmo|accountant|ard|cashier')
    <div class="card">
        <div class="card-header">
            <h3>💰 Payroll</h3>
            <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body">
            <p class="text-muted" style="margin:0; font-size:0.88rem;">
                No payroll batches loaded yet. Create a new batch to begin processing
                the {{ now()->format('F Y') }} payroll.
            </p>
            @role('payroll_officer|hrmo')
            <div style="margin-top:14px;">
                <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm">
                    + New Payroll Batch
                </a>
            </div>
            @endrole
        </div>
    </div>
    @endrole

    {{-- TEV Quick Access --}}
    @role('payroll_officer|hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer')
    <div class="card">
        <div class="card-header">
            <h3>✈ Travel (TEV)</h3>
            <a href="{{ route('tev.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body">
            <p class="text-muted" style="margin:0; font-size:0.88rem;">
                No pending TEV requests. Office Orders and TEV filings will appear here
                once submitted for approval.
            </p>
            <div style="margin-top:14px;">
                <a href="{{ route('office-orders.create') }}" class="btn btn-primary btn-sm">
                    + New Office Order
                </a>
            </div>
        </div>
    </div>
    @endrole

</div>

{{-- System Info --}}
<div class="card">
    <div class="card-header">
        <h3>⚙ System Information</h3>
    </div>
    <div class="card-body">
        <table style="width:auto; font-size:0.875rem;">
            <tr>
                <td style="padding:5px 24px 5px 0; color:var(--text-light); font-weight:600;">Laravel Version</td>
                <td style="padding:5px 0;">{{ app()->version() }}</td>
            </tr>
            <tr>
                <td style="padding:5px 24px 5px 0; color:var(--text-light); font-weight:600;">PHP Version</td>
                <td style="padding:5px 0;">{{ PHP_VERSION }}</td>
            </tr>
            <tr>
                <td style="padding:5px 24px 5px 0; color:var(--text-light); font-weight:600;">Environment</td>
                <td style="padding:5px 0;">{{ config('app.env') }}</td>
            </tr>
            <tr>
                <td style="padding:5px 24px 5px 0; color:var(--text-light); font-weight:600;">Logged in as</td>
                <td style="padding:5px 0;">
                    {{ auth()->user()->name }}
                    &mdash;
                    <span class="role-badge">{{ auth()->user()->getRoleNames()->first() }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding:5px 24px 5px 0; color:var(--text-light); font-weight:600;">Server Time</td>
                <td style="padding:5px 0;">{{ now()->format('D, d M Y H:i:s T') }}</td>
            </tr>
        </table>
    </div>
</div>

@endsection

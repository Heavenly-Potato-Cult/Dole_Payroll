@extends('layouts.app')

@section('title', 'My Payslip')
@section('page-title', 'My Payslip')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>My Payslip</h1>
        <p>Your payroll slips from released batches</p>
    </div>
</div>

@if ($entries->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 48px; margin-bottom: 16px;">📄</div>
        <h3 style="color: var(--text-mid); margin-bottom: 8px;">No Payslips Available</h3>
        <p style="color: var(--text-light);">Your payslips will appear here once payroll batches are released.</p>
    </div>
@else
    <div class="card">
        <div class="card-header">
            <h3>💰 My Payslips</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Payroll Period</th>
                            <th>Cutoff</th>
                            <th>Gross Income</th>
                            <th>Total Deductions</th>
                            <th>Net Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr>
                                <td>
                                    {{ $entry->batch->period_year }} - 
                                    {{ \Carbon\Carbon::createFromFormat('m', $entry->batch->period_month)->format('F') }}
                                </td>
                                <td>{{ ucfirst($entry->batch->cutoff) }}</td>
                                <td>₱{{ number_format($entry->gross_income, 2) }}</td>
                                <td>₱{{ number_format($entry->total_deductions, 2) }}</td>
                                <td><strong>₱{{ number_format($entry->net_amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge badge-{{ $entry->batch->status }}">
                                        {{ ucfirst($entry->batch->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('payroll.payslip', [$entry->batch, $entry]) }}" 
                                       class="btn btn-sm btn-primary" target="_blank">
                                        📄 View Payslip
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="margin-top: 12px;">
        {{ $entries->links() }}
    </div>
@endif

@endsection

@section('styles')
<style>
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.badge-released {
    background: #d4edda;
    color: #155724;
}
.badge-locked {
    background: #cce5ff;
    color: #004085;
}
.table {
    width: 100%;
    border-collapse: collapse;
}
.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.table th {
    background: var(--surface);
    font-weight: 600;
    color: var(--text-mid);
}
.table tbody tr:hover {
    background: var(--surface);
}
</style>
@endsection

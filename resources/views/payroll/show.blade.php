@extends('layouts.app')

@section('title', 'Payroll Batch Detail')
@section('page-title', 'Payroll Batch')

@section('content')

@php
    $months = ['','January','February','March','April','May','June',
               'July','August','September','October','November','December'];
    $periodLabel = $months[$payroll->period_month] . ' ' .
                   ($payroll->cutoff === '1st' ? '1–15' : '16–30/31') .
                   ', ' . $payroll->period_year;

    $statusClass = match($payroll->status) {
        'draft'              => 'badge-draft',
        'computed'           => 'badge-computed',
        'pending_accountant',
        'pending_rd'         => 'badge-pending',
        'released'           => 'badge-released',
        'locked'             => 'badge-locked',
        default              => 'badge-draft',
    };
    $statusLabel = ucfirst(str_replace('_', ' ', $payroll->status));

    $isLocked = $payroll->status === 'locked';
    $canCompute = in_array($payroll->status, ['draft','computed'])
                  && auth()->user()->hasAnyRole(['payroll_officer','hrmo']);
@endphp

{{-- ── Page header ── --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $periodLabel }}</h1>
        <p>
            {{ $payroll->cutoff }} cut-off ·
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            · Created by {{ $payroll->creator->name ?? '—' }}
            on {{ $payroll->created_at->format('M d, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">← All Batches</a>

        {{-- Compute / Re-compute --}}
        @if ($canCompute)
        <form method="POST" action="{{ route('payroll.compute', $payroll) }}"
              onsubmit="return confirm('Run payroll computation for all active employees?\n\nExisting entries for this batch will be overwritten.')">
            @csrf
            <button class="btn btn-gold btn-sm">
                ⚙ {{ $payroll->status === 'draft' ? 'Compute Payroll' : 'Re-compute' }}
            </button>
        </form>
        @endif

        {{-- Approval action button --}}
        @if ($nextAction)
        <form method="POST" action="{{ route('payroll.approve', $payroll) }}"
              onsubmit="return confirm('{{ $nextAction['label'] }}?\n\nThis action cannot be undone.')">
            @csrf
            <button class="btn btn-primary btn-sm">✔ {{ $nextAction['label'] }}</button>
        </form>
        @endif

        {{-- Reports (only when computed or beyond) --}}
        @if (!in_array($payroll->status, ['draft']))
        <a href="{{ route('reports.payroll-register', ['batch_id' => $payroll->id]) }}"
           class="btn btn-outline btn-sm" target="_blank">
            📄 Payroll Register PDF
        </a>
        @endif
    </div>
</div>

{{-- ── Approval stage bar ── --}}
@include('payroll._approval_bar', ['payroll' => $payroll])

{{-- ── Stat cards ── --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Employees</div>
        <div class="stat-value">{{ $employeeCount }}</div>
        <div class="stat-sub">Active regular employees</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Total Gross</div>
        <div class="stat-value">₱{{ number_format($totalGross, 0) }}</div>
        <div class="stat-sub">Basic + PERA + RATA</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Total Deductions</div>
        <div class="stat-value">₱{{ number_format($totalDeds, 0) }}</div>
        <div class="stat-sub">All deduction lines</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Total Net Pay</div>
        <div class="stat-value">₱{{ number_format($totalNet, 0) }}</div>
        <div class="stat-sub">Gross − Total Deductions</div>
    </div>
</div>

@if ($payroll->status === 'draft' && $employeeCount === 0)
<div class="alert alert-warning">
    No employee entries yet. Click <strong>Compute Payroll</strong> above to generate all entries.
</div>
@endif

{{-- ── Employee entries table ── --}}
@if ($employeeCount > 0)
<div class="card">
    <div class="card-header">
        <h3>Employee Payroll Entries ({{ $employeeCount }})</h3>
        @if (!$isLocked)
        <span class="text-muted" style="font-size:0.79rem;">
            Click an employee row to view their full payslip.
        </span>
        @endif
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Position</th>
                        <th class="text-right">Basic Earned</th>
                        <th class="text-right">PERA</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Tardiness</th>
                        <th class="text-right">Total Deductions</th>
                        <th class="text-right">Net Pay</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $i => $entry)
                    @php
                        $netWarning = $entry->net_amount < 5000;  // LBP loan threshold
                    @endphp
                    <tr style="{{ $netWarning ? 'background:#FFF8E1;' : '' }}">
                        <td class="text-muted" style="font-size:0.78rem;">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-bold" style="font-size:0.88rem;">
                                {{ $entry->employee->full_name }}
                            </div>
                            <div class="text-muted" style="font-size:0.76rem;">
                                {{ $entry->employee->plantilla_item_no ?? '—' }}
                            </div>
                        </td>
                        <td style="font-size:0.83rem; color:var(--text-mid);">
                            {{ $entry->employee->position_title }}
                        </td>
                        <td class="text-right">₱{{ number_format($entry->basic_salary, 2) }}</td>
                        <td class="text-right">₱{{ number_format($entry->pera, 2) }}</td>
                        <td class="text-right">₱{{ number_format($entry->gross_income, 2) }}</td>
                        <td class="text-right {{ ($entry->tardiness + $entry->undertime) > 0 ? 'text-red' : '' }}">
                            ₱{{ number_format($entry->tardiness + $entry->undertime, 2) }}
                        </td>
                        <td class="text-right">₱{{ number_format($entry->total_deductions, 2) }}</td>
                        <td class="text-right fw-bold {{ $netWarning ? 'text-red' : '' }}">
                            ₱{{ number_format($entry->net_amount, 2) }}
                            @if ($netWarning)
                                <span class="badge badge-pending" style="font-size:0.68rem; display:block; margin-top:2px;">
                                    Below ₱5K
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('payroll.payslip', [$payroll, $entry]) }}"
                               class="btn btn-outline btn-sm" target="_blank">
                                Payslip
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--navy); color:white; font-weight:700;">
                        <td colspan="5" style="padding:10px 14px; text-align:right; color:white;">
                            TOTALS ({{ $employeeCount }} employees)
                        </td>
                        <td style="padding:10px 14px; text-align:right; color:white;">
                            ₱{{ number_format($totalGross, 2) }}
                        </td>
                        <td style="padding:10px 14px; text-align:right; color:var(--gold);">—</td>
                        <td style="padding:10px 14px; text-align:right; color:var(--gold);">
                            ₱{{ number_format($totalDeds, 2) }}
                        </td>
                        <td style="padding:10px 14px; text-align:right; color:var(--gold);">
                            ₱{{ number_format($totalNet, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

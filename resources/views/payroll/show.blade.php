{{-- resources/views/payroll/show.blade.php --}}
{{--
    Expects from PayrollController@show:
      $payroll        — PayrollBatch (with entries.employee, entries.deductions, creator, auditLogs.user)
      $entries        — sorted collection
      $totalGross, $totalDeds, $totalNet, $employeeCount
      $auditLogs
--}}

@extends('layouts.app')

@section('title', 'Payroll Batch Detail')
@section('page-title', 'Payroll Batch')

@section('styles')
<style>
/* ══════════════════════════════════════════════════
   APPROVAL STAGE BAR
══════════════════════════════════════════════════ */
.approval-bar {
    display: flex;
    align-items: stretch;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 24px;
}
.approval-step {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    font-size: 0.80rem;
    font-weight: 600;
    color: var(--text-light);
    background: var(--surface);
    border-right: 1px solid var(--border);
    transition: background 0.2s;
}
.approval-step:last-child { border-right: none; }
.approval-step.done   { background: #F1FAF5; color: #1B6B3A; }
.approval-step.active { background: #EEF1FA; color: var(--navy); }
.approval-step.locked { background: var(--navy); color: #ffffff; }
.approval-step-dot {
    width: 30px; height: 30px;
    border-radius: 50%;
    border: 2px solid currentColor;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; font-weight: 700;
    flex-shrink: 0;
    background: #ffffff; color: inherit;
}
.approval-step.done   .approval-step-dot { background: #2E7D52; border-color: #2E7D52; color: #ffffff; }
.approval-step.active .approval-step-dot { background: var(--navy); border-color: var(--navy); color: #ffffff; }
.approval-step.locked .approval-step-dot { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.6); color: #ffffff; }
.approval-step-label { line-height: 1.3; min-width: 0; }
.approval-step-label small {
    display: block; font-weight: 400; font-size: 0.70rem;
    opacity: 0.72; margin-top: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── Deduction expansion panel ── */
.ded-toggle {
    background: none; border: 1px solid var(--border);
    color: var(--navy); border-radius: 4px;
    padding: 2px 8px; font-size: 0.73rem;
    cursor: pointer; white-space: nowrap;
}
.ded-toggle:hover { background: var(--navy-light); }
.ded-panel { display: none; background: var(--bg); border-top: 1px solid var(--border); padding: 10px 14px; }
.ded-panel.open { display: block; }
.ded-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 4px 16px; font-size: 0.76rem;
}
.ded-row {
    display: flex; justify-content: space-between;
    padding: 2px 0; border-bottom: 1px solid var(--border);
    color: var(--text-mid);
}
.ded-row span:last-child { font-weight: 600; color: var(--text); }
.tfoot-totals td { padding: 12px 14px; font-weight: 700; font-size: 0.88rem; }
.net-warn { background: #FFF8E1 !important; }
.net-warn-badge {
    display: inline-block; margin-top: 3px;
    font-size: 0.67rem; background: #FFE082; color: #7A5900;
    padding: 1px 6px; border-radius: 10px;
    font-weight: 700; letter-spacing: 0.03em;
}
.scroll-hint { font-size: 0.75rem; color: var(--text-light); padding: 6px 14px 0; }
.empty-state { text-align: center; padding: 60px 20px; color: var(--text-light); }
.empty-state-icon { font-size: 2.5rem; margin-bottom: 12px; }
.empty-state h3 { color: var(--text-mid); margin-bottom: 8px; }

/* ── Audit log ── */
.audit-table td { font-size: 0.80rem; vertical-align: top; }
.audit-arrow { color: var(--text-light); margin: 0 4px; }

/* ══════════════════════════════════════════════════
   MOBILE RESPONSIVE
══════════════════════════════════════════════════ */
@media (max-width: 768px) {

    /* Page header: stack vertically */
    .page-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    .page-header > .d-flex {
        width: 100%;
        flex-wrap: wrap;
    }
    .page-header > .d-flex .btn {
        flex: 1;
        justify-content: center;
        text-align: center;
        min-width: calc(50% - 4px);
    }

    /* Approval bar: scroll horizontally on mobile */
    .approval-bar {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .approval-step {
        min-width: 130px;
        flex: 0 0 auto;
    }

    /* Stat grid: 2 columns on mobile */
    .stat-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
    }
    .stat-card { padding: 14px !important; }
    .stat-value { font-size: 1.2rem !important; }

    /* Payroll register table: keep horizontal scroll with sticky # column */
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* Scroll hint more visible */
    .scroll-hint {
        background: var(--bg);
        border-bottom: 1px solid var(--border);
        padding: 8px 14px;
        font-size: 0.78rem;
        color: var(--text-mid);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .scroll-hint::before { content: '↔'; font-size: 1rem; }

    /* Make action buttons in header stack into 2-col grid */
    .payroll-show-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
    }
    .payroll-show-actions .btn,
    .payroll-show-actions form { width: 100%; }
    .payroll-show-actions form .btn { width: 100%; }

    /* Certification footer: stack */
    .cert-footer > div {
        flex-direction: column !important;
        gap: 10px !important;
    }

    /* Audit table: horizontal scroll */
    .audit-table { min-width: 600px; }
}
</style>
@endsection

@section('content')

@php
    $months = [
        '', 'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];
    $periodLabel = ($months[$payroll->period_month] ?? '?')
        . ' ' . ($payroll->cutoff === '1st' ? '1–15' : '16–30/31')
        . ', ' . $payroll->period_year;

    $statusClass = match ($payroll->status) {
        'draft'              => 'badge-draft',
        'computed'           => 'badge-computed',
        'pending_accountant',
        'pending_rd'         => 'badge-pending',
        'released'           => 'badge-released',
        'locked'             => 'badge-locked',
        default              => 'badge-draft',
    };
    $statusLabels = [
        'draft'               => 'Draft',
        'computed'            => 'Computed',
        'pending_accountant'  => 'Pending Accountant',
        'pending_rd'          => 'Pending RD / ARD',
        'released'            => 'Released',
        'locked'              => 'Locked',
    ];
    $statusLabel = $statusLabels[$payroll->status] ?? ucfirst(str_replace('_', ' ', $payroll->status));

    $isLocked   = $payroll->status === 'locked';
    $isComputed = ! in_array($payroll->status, ['draft']);

    // CHANGED: hrmo removed — only payroll_officer may compute / re-compute
    $canCompute = in_array($payroll->status, ['draft', 'computed'])
               && auth()->user()->hasRole('payroll_officer');

    $canPullAttendance = in_array($payroll->status, ['draft', 'computed'])
                  && auth()->user()->hasRole('payroll_officer');

    $nextAction = null;
    // CHANGED: hrmo removed — only payroll_officer submits to Accountant
    if (auth()->user()->hasRole('payroll_officer')
        && in_array($payroll->status, ['draft', 'computed'])) {
        $nextAction = [
            'label'  => 'Submit to Accountant',
            'route'  => route('payroll.submit', $payroll),
            'class'  => 'btn-primary',
            'confirm'=> 'Submit this payroll batch to the Accountant for review?',
        ];
    } elseif (auth()->user()->hasRole('accountant')
              && $payroll->status === 'pending_accountant') {
        $nextAction = [
            'label'  => 'Certify & Forward to RD/ARD',
            'route'  => route('payroll.certify', $payroll),
            'class'  => 'btn-primary',
            'confirm'=> 'Certify funds and forward to RD/ARD for approval?',
        ];
    } elseif (auth()->user()->hasAnyRole(['ard', 'chief_admin_officer'])
              && $payroll->status === 'pending_rd') {
        $nextAction = [
            'label'  => 'Approve & Release',
            'route'  => route('payroll.approve', $payroll),
            'class'  => 'btn-gold',
            'confirm'=> 'Approve and release this payroll batch?',
        ];
    } elseif (auth()->user()->hasRole('cashier')
              && $payroll->status === 'released') {
        $nextAction = [
            'label'  => 'Lock — Disbursement Complete',
            'route'  => route('payroll.lock', $payroll),
            'class'  => 'btn-danger',
            'confirm'=> 'Lock this payroll batch? This marks disbursement as complete and cannot be undone.',
        ];
    }
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════ --}}
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
    <div class="d-flex gap-2 flex-wrap payroll-show-actions">
        <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">← All Batches</a>

@if ($canPullAttendance)
    <form method="POST" action="{{ route('payroll.pullAttendance', $payroll) }}"
          onsubmit="return confirm('{{ $snapshotCount > 0 ? 'Re-pulling will reset any manual HR corrections. Continue?' : 'Pull attendance from HRIS for all active employees?' }}')">
        @csrf
        <button class="btn btn-outline btn-sm">
            {{ $snapshotCount > 0 ? '🔄 Re-pull Attendance' : '📥 Pull Attendance' }}
            @if ($snapshotCount > 0)
                <span style="font-size:0.72rem; opacity:0.8;">({{ $snapshotCount }}/{{ $activeCount }})</span>
            @endif
        </button>
    </form>
@endif

@if ($canCompute)
    <form method="POST" action="{{ route('payroll.compute', $payroll) }}"
          onsubmit="return confirm('Run payroll computation for all active employees?\n\nExisting entries will be overwritten.')">
        @csrf
        @if ($snapshotCount === 0)
            <button class="btn btn-gold btn-sm" disabled title="Pull attendance first">
                ⚙ {{ $payroll->status === 'draft' ? 'Compute Payroll' : 'Re-compute' }}
            </button>
        @else
            <button class="btn btn-gold btn-sm">
                ⚙ {{ $payroll->status === 'draft' ? 'Compute Payroll' : 'Re-compute' }}
            </button>
        @endif
    </form>
@endif

        @if ($nextAction)
            <form method="POST" action="{{ $nextAction['route'] }}"
                  onsubmit="return confirm('{{ $nextAction['confirm'] }}')">
                @csrf
                <button class="btn {{ $nextAction['class'] }} btn-sm">
                    ✔ {{ $nextAction['label'] }}
                </button>
            </form>
        @endif

      {{-- NEW: --}}
@if ($isComputed)
    <a href="{{ route('reports.payroll-register', ['batch_id' => $payroll->id]) }}"
       class="btn btn-outline btn-sm" target="_blank">
        📄 Payroll Register PDF
    </a>
@endif

{{-- Payslip generation — only after release --}}
@if (in_array($payroll->status, ['released', 'locked']))
    <button class="btn btn-outline btn-sm" onclick="openPayslipModal()">
        🧾 Generate Payslips
    </button>
@elseif ($isComputed)
    <button class="btn btn-outline btn-sm" disabled
            title="Payslips available after the batch is released"
            style="opacity:0.45; cursor:not-allowed;">
        🧾 Payslips (Pending Release)
    </button>
@endif

        @if ($payroll->status === 'released' || auth()->user()->hasRole('cashier'))
            <a href="{{ route('payroll.verify', $payroll) }}" class="btn btn-outline btn-sm">
                📋 Verify Net Pay
            </a>
        @endif
    </div>
</div>

{{-- Alerts --}}
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if (session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     APPROVAL STAGE BAR
═══════════════════════════════════════════════════════════════ --}}
@include('payroll._approval_bar')

{{-- ═══════════════════════════════════════════════════════════════
     SUMMARY STAT CARDS
═══════════════════════════════════════════════════════════════ --}}
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

{{-- ═══════════════════════════════════════════════════════════════
     ATTENDANCE PANEL (draft / computed only)
═══════════════════════════════════════════════════════════════ --}}
@if (in_array($payroll->status, ['draft', 'computed']))
<div class="card" style="margin-bottom:20px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>📋 Attendance Data</h3>
        @if ($snapshotCount > 0)
            <span class="badge badge-computed" style="font-size:0.75rem;">
                {{ $snapshotCount }}/{{ $activeCount }} pulled
                @if ($correctedCount > 0) · {{ $correctedCount }} corrected @endif
            </span>
        @else
            <span class="badge badge-draft" style="font-size:0.75rem;">Not pulled yet</span>
        @endif
    </div>
    <div class="card-body">

        @if ($snapshotCount === 0)
            <div class="alert alert-warning" style="margin-bottom:0;">
                <strong>⚠ Attendance has not been pulled yet.</strong>
                The Compute button is disabled until attendance is pulled.
                Without this step all employees would be computed with zero tardiness and zero LWOP.
            </div>
        @elseif ($snapshotCount < $activeCount)
            <div class="alert alert-warning" style="margin-bottom:12px;">
                <strong>⚠ Partial pull:</strong> {{ $snapshotCount }} of {{ $activeCount }} employees have attendance data. Consider re-pulling.
            </div>
        @else
            <div class="alert" style="background:#F1FAF5; border-color:#A8D5B5; margin-bottom:12px;">
                ✅ Attendance pulled for all {{ $snapshotCount }} employees.
                @if ($correctedCount > 0)
                    <strong>{{ $correctedCount }} record(s) manually corrected by HR.</strong>
                @endif
                Review below, then compute.
            </div>
        @endif

        @if ($snapshots->count() > 0)
            <button type="button" class="btn btn-outline btn-sm" style="margin-bottom:12px;"
                    onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                👁 Show / Hide Attendance Records ({{ $snapshots->count() }})
            </button>
            <div style="display:none; overflow-x:auto;">
                <table style="font-size:0.82rem; min-width:600px; width:100%;">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="text-center">Days Present</th>
                            <th class="text-center">LWOP Days</th>
                            <th class="text-center">Late (min)</th>
                            <th class="text-center">Undertime (min)</th>
                            <th class="text-center">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($snapshots as $snap)
                            <tr style="{{ $snap->is_corrected ? 'background:#FFF8E1;' : '' }}">
                                <td>
                                    <div class="fw-bold" style="font-size:0.83rem;">
                                        {{ optional($snap->employee)->last_name }}, {{ optional($snap->employee)->first_name }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.72rem;">{{ optional($snap->employee)->employee_no }}</div>
                                </td>
                                <td class="text-center">{{ number_format($snap->days_present, 1) }}</td>
                                <td class="text-center {{ $snap->lwop_days > 0 ? 'text-red fw-bold' : '' }}">
                                    {{ number_format($snap->lwop_days, 3) }}
                                </td>
                                <td class="text-center {{ $snap->late_minutes > 0 ? 'text-red' : '' }}">
                                    {{ $snap->late_minutes }}
                                </td>
                                <td class="text-center {{ $snap->undertime_minutes > 0 ? 'text-red' : '' }}">
                                    {{ $snap->undertime_minutes }}
                                </td>
                                <td class="text-center">
                                    @if ($snap->is_corrected)
                                        <span class="badge badge-pending" title="{{ $snap->correction_note }}">✏ HR Corrected</span>
                                    @else
                                        <span class="badge badge-draft">HRIS API</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endif

@if ($payroll->status === 'draft' && $employeeCount === 0)
    <div class="alert alert-warning">
        No entries yet. Click <strong>Compute Payroll</strong> above to generate all employee entries.
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PAYROLL REGISTER TABLE
═══════════════════════════════════════════════════════════════ --}}
@if ($employeeCount > 0)
<div class="card" style="overflow:visible;">
    <div class="card-header">
        <h3>Payroll Register — {{ $periodLabel }} ({{ $employeeCount }} Employees)</h3>
<div class="d-flex gap-2 align-center flex-wrap">
    <span class="text-muted" style="font-size:0.78rem;">
        Click <em>Deductions ▾</em> to expand per-employee breakdown.
    </span>
    @if (in_array($payroll->status, ['released', 'locked']))
        <span class="text-muted" style="font-size:0.78rem;">
            · Click <em>Payslip</em> to view / print individual slips.
        </span>
    @endif
</div>
    </div>

    <div class="scroll-hint">Scroll horizontally to see all columns</div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table id="payrollRegisterTable">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th>Employee</th>
                        <th>SG–Step</th>
                        <th class="text-right">Basic Earned</th>
                        <th class="text-right">PERA</th>
                        <th class="text-right">RATA</th>
                        <th class="text-right" style="background:rgba(249,168,37,0.22); color:var(--gold-dark);">Gross</th>
                        <th class="text-right">Tardiness</th>
                        <th class="text-right">LWOP</th>
                        <th class="text-right">Ded. Lines</th>
                        <th class="text-right" style="background:rgba(183,28,28,0.12); color:#8B0000;">Total Ded.</th>
                        <th class="text-right" style="background:rgba(27,94,32,0.12); color:#1B5E20;">Net Pay</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $i => $entry)
                        @php
                            $netWarn  = $entry->net_amount < 5000;
                            $tardy    = ($entry->tardiness ?? 0) + ($entry->undertime ?? 0);
                            $lwop     = $entry->lwop_deduction ?? 0;
                            $dedCount = $entry->deductions->count();
                        @endphp

                        <tr class="{{ $netWarn ? 'net-warn' : '' }}" id="row-{{ $entry->id }}">
                            <td class="text-muted" style="font-size:0.75rem;">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-bold" style="font-size:0.86rem; white-space:nowrap;">
                                    {{ $entry->employee->full_name }}
                                </div>
                                <div class="text-muted" style="font-size:0.73rem;">
                                    {{ $entry->employee->position_title }}
                                </div>
                            </td>
                            <td style="font-size:0.82rem; white-space:nowrap;">
                                SG {{ $entry->employee->salary_grade }}–{{ $entry->employee->step }}
                            </td>
                            <td class="text-right" style="white-space:nowrap;">
                                ₱{{ number_format($entry->basic_salary, 2) }}
                            </td>
                            <td class="text-right" style="white-space:nowrap;">
                                ₱{{ number_format($entry->pera, 2) }}
                            </td>
                            <td class="text-right" style="white-space:nowrap; color:var(--text-light);">
                                {{ $entry->rata > 0 ? '₱' . number_format($entry->rata, 2) : '—' }}
                            </td>
                            <td class="text-right fw-bold" style="white-space:nowrap; background:rgba(249,168,37,0.06);">
                                ₱{{ number_format($entry->gross_income, 2) }}
                            </td>
                            <td class="text-right {{ $tardy > 0 ? 'text-red' : '' }}" style="white-space:nowrap;">
                                {{ $tardy > 0 ? '₱' . number_format($tardy, 2) : '—' }}
                            </td>
                            <td class="text-right {{ $lwop > 0 ? 'text-red' : '' }}" style="white-space:nowrap;">
                                {{ $lwop > 0 ? '₱' . number_format($lwop, 2) : '—' }}
                            </td>
                            <td class="text-right" style="white-space:nowrap;">
                                @if ($dedCount > 0)
<button class="ded-toggle"
        onclick="toggleDed({{ $entry->id }})"
        id="toggle-{{ $entry->id }}"
        data-count="{{ $dedCount }}">
    {{ $dedCount }} lines ▾
</button>
                                @else
                                    <span class="text-muted" style="font-size:0.78rem;">—</span>
                                @endif
                            </td>
                            <td class="text-right" style="white-space:nowrap; background:rgba(183,28,28,0.04);">
                                ₱{{ number_format($entry->total_deductions, 2) }}
                            </td>
                            <td class="text-right fw-bold {{ $netWarn ? 'text-red' : '' }}"
                                style="white-space:nowrap; background:rgba(27,94,32,0.04);">
                                ₱{{ number_format($entry->net_amount, 2) }}
                                @if ($netWarn)
                                    <span class="net-warn-badge">Below ₱5K</span>
                                @endif
                            </td>
{{-- NEW --}}
<td>
    @if (in_array($payroll->status, ['released', 'locked']))
        <a href="{{ route('payroll.payslip', [$payroll, $entry]) }}"
           class="btn btn-outline btn-sm" target="_blank">
            Payslip
        </a>
    @else
        <span class="text-muted" style="font-size:0.75rem;">—</span>
    @endif
</td>
                        </tr>

                        @if ($dedCount > 0)
<tr class="{{ $netWarn ? 'net-warn' : '' }}"
    id="ded-row-{{ $entry->id }}" hidden>
    <td colspan="13" style="padding:0;">
        <div class="ded-panel" id="ded-panel-{{ $entry->id }}" hidden>
                                        <div class="ded-grid">
                                            @foreach ($entry->deductions->sortBy(fn ($d) => optional($d->deductionType)->display_order ?? 99) as $ded)
                                                <div class="ded-row">
                                                    <span>{{ $ded->name }}</span>
                                                    <span>₱{{ number_format($ded->amount, 2) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div style="text-align:right; margin-top:6px; font-size:0.78rem; color:var(--text-mid);">
                                            Sub-total:
                                            <strong>₱{{ number_format($entry->deductions->sum('amount'), 2) }}</strong>
                                            @if (($entry->tardiness + $entry->undertime + $entry->lwop_deduction) > 0)
                                                · Attendance deduction:
                                                <strong class="text-red">
                                                    ₱{{ number_format($entry->tardiness + $entry->undertime + ($entry->lwop_deduction ?? 0), 2) }}
                                                </strong>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="tfoot-totals" style="background:var(--navy); color:white;">
                        <td colspan="3" style="padding:12px 14px; color:rgba(255,255,255,0.7); font-size:0.82rem;">
                            TOTALS — {{ $employeeCount }} employee{{ $employeeCount !== 1 ? 's' : '' }}
                        </td>
                        <td class="text-right" style="color:white;">
                            ₱{{ number_format($payroll->entries->sum('basic_salary'), 2) }}
                        </td>
                        <td class="text-right" style="color:white;">
                            ₱{{ number_format($payroll->entries->sum('pera'), 2) }}
                        </td>
                        <td class="text-right" style="color:rgba(255,255,255,0.5);">
                            {{ $payroll->entries->sum('rata') > 0
                               ? '₱' . number_format($payroll->entries->sum('rata'), 2) : '—' }}
                        </td>
                        <td class="text-right" style="color:var(--gold); background:rgba(249,168,37,0.15);">
                            ₱{{ number_format($totalGross, 2) }}
                        </td>
                        <td class="text-right" style="color:rgba(255,255,255,0.7);">
                            ₱{{ number_format($payroll->entries->sum('tardiness') + $payroll->entries->sum('undertime'), 2) }}
                        </td>
                        <td class="text-right" style="color:rgba(255,255,255,0.7);">
                            ₱{{ number_format($payroll->entries->sum('lwop_deduction'), 2) }}
                        </td>
                        <td></td>
                        <td class="text-right" style="color:var(--gold);">
                            ₱{{ number_format($totalDeds, 2) }}
                        </td>
                        <td class="text-right" style="color:#69F0AE; font-size:1rem;">
                            ₱{{ number_format($totalNet, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($isComputed)
        <div class="card-body cert-footer" style="background:#FAFBFF; border-top:1px solid var(--border); padding:14px 20px;">
            <div class="d-flex gap-2 flex-wrap"
                 style="justify-content:space-between; align-items:flex-end; font-size:0.82rem; color:var(--text-mid);">
                <div>
                    <strong>Prepared by:</strong>
                    {{ $payroll->creator->name ?? '—' }}
                    <span class="text-muted">· {{ $payroll->created_at->format('M d, Y') }}</span>
                </div>
                @if ($payroll->approved_by)
                    <div>
                        <strong>Approved by:</strong>
                        {{ optional($payroll->approver)->name ?? '—' }}
                        @if ($payroll->approved_at)
                            <span class="text-muted">· {{ \Carbon\Carbon::parse($payroll->approved_at)->format('M d, Y') }}</span>
                        @endif
                    </div>
                @endif
                @if ($payroll->released_at)
                    <div>
                        <strong>Released:</strong>
                        <span class="text-muted">{{ \Carbon\Carbon::parse($payroll->released_at)->format('M d, Y g:i A') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@else

<div class="card">
    <div class="card-body empty-state">
        <div class="empty-state-icon">📊</div>
        <h3>No Entries Yet</h3>
        <p>Click <strong>Compute Payroll</strong> above to generate entries for all active employees.</p>
    </div>
</div>

@endif

{{-- ═══════════════════════════════════════════════════════════════
     AUDIT LOG
═══════════════════════════════════════════════════════════════ --}}
@if ($auditLogs->isNotEmpty())
<div class="card" style="margin-top:24px;">
    <div class="card-header">
        <h3>Audit Log</h3>
        <span class="text-muted" style="font-size:0.80rem;">
            {{ $auditLogs->count() }} entr{{ $auditLogs->count() === 1 ? 'y' : 'ies' }}
        </span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Status Change</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($auditLogs as $log)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($log->performed_at)->format('M d, Y g:i A') }}
                            </td>
                            <td>{{ $log->user->name ?? '—' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>
                                @if ($log->old_value || $log->new_value)
                                    <span class="badge badge-draft" style="font-size:0.70rem;">
                                        {{ $log->old_value ?? '—' }}
                                    </span>
                                    <span class="audit-arrow">→</span>
                                    <span class="badge badge-computed" style="font-size:0.70rem;">
                                        {{ $log->new_value ?? '—' }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PAYSLIP GENERATION MODAL
═══════════════════════════════════════════════════════════════ --}}
<div id="payslipModal" style="
    display:none; position:fixed; inset:0; z-index:1000;
    background:rgba(0,0,0,0.45); align-items:center; justify-content:center;">
    <div style="
        background:#fff; border-radius:var(--radius); box-shadow:0 8px 32px rgba(0,0,0,0.18);
        padding:28px 32px; width:100%; max-width:440px; margin:16px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="color:var(--navy); margin:0;">Generate Payslips</h3>
            <button onclick="closePayslipModal()"
                    style="background:none; border:none; font-size:1.4rem; color:var(--text-light);
                           cursor:pointer; line-height:1;">&times;</button>
        </div>

        <p style="font-size:0.85rem; color:var(--text-mid); margin-bottom:20px;">
            <strong>{{ $periodLabel }}</strong> ·
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </p>

        {{-- Option A: Monthly consolidated (default) --}}
        <label id="opt-consolidated" class="payslip-opt selected"
               style="display:flex; align-items:flex-start; gap:12px; padding:14px 16px;
                      border:2px solid var(--navy); border-radius:var(--radius);
                      cursor:pointer; margin-bottom:10px; transition:border-color .15s;">
            <input type="radio" name="payslipMode" value="consolidated"
                   checked onchange="selectOpt('consolidated')"
                   style="margin-top:3px; accent-color:var(--navy);">
            <div>
                <div style="font-weight:700; font-size:0.88rem; color:var(--navy);">
                    Monthly Payslip
                    <span style="font-size:0.72rem; background:#E8F0FE; color:var(--navy);
                                 padding:1px 8px; border-radius:10px; margin-left:6px;">
                        Recommended
                    </span>
                </div>
                <div style="font-size:0.78rem; color:var(--text-mid); margin-top:3px;">
                    Single payslip showing both 1–15 and 16–30/31 cut-offs side by side.
                    Matches current DOLE practice.
                </div>
            </div>
        </label>

        {{-- Option B: Per batch --}}
        <label id="opt-perbatch" class="payslip-opt"
               style="display:flex; align-items:flex-start; gap:12px; padding:14px 16px;
                      border:2px solid var(--border); border-radius:var(--radius);
                      cursor:pointer; margin-bottom:20px; transition:border-color .15s;">
            <input type="radio" name="payslipMode" value="per_batch"
                   onchange="selectOpt('per_batch')"
                   style="margin-top:3px; accent-color:var(--navy);">
            <div>
                <div style="font-weight:700; font-size:0.88rem; color:var(--navy);">
                    Per Batch (Separate)
                </div>
                <div style="font-size:0.78rem; color:var(--text-mid); margin-top:3px;">
                    Generate individual payslips for the 1st and 2nd cut-offs separately.
                </div>
            </div>
        </label>

        {{-- Employee filter (optional) --}}
        <div style="margin-bottom:20px;">
            <label style="font-size:0.75rem; font-weight:700; text-transform:uppercase;
                          letter-spacing:.05em; color:var(--text-mid); display:block; margin-bottom:6px;">
                Employee (leave blank for all)
            </label>
            <select id="payslipEmployee"
                    style="width:100%; height:38px; border:1px solid var(--border);
                           border-radius:var(--radius); padding:0 10px; font-size:0.85rem;">
                <option value="">— All Employees —</option>
                @foreach ($entries as $entry)
                    <option value="{{ $entry->id }}">{{ $entry->employee->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closePayslipModal()" class="btn btn-outline btn-sm">Cancel</button>
            <button onclick="submitPayslip()" class="btn btn-primary btn-sm">
                📄 Generate PDF
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleDed(entryId) {
    const row    = document.getElementById('ded-row-' + entryId);
    const panel  = document.getElementById('ded-panel-' + entryId);
    const toggle = document.getElementById('toggle-' + entryId);
    if (!row || !panel || !toggle) return;

    const isOpen = !row.hidden;

    row.hidden   = isOpen;
    panel.hidden = isOpen;

    toggle.dataset.count = toggle.dataset.count || toggle.textContent.match(/\d+/)?.[0] || '?';
    toggle.textContent = toggle.dataset.count + ' lines ' + (isOpen ? '▾' : '▴');
}


// ── Payslip modal ──────────────────────────────────────────
function openPayslipModal() {
    const m = document.getElementById('payslipModal');
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closePayslipModal() {
    const m = document.getElementById('payslipModal');
    m.style.display = 'none';
    document.body.style.overflow = '';
}
function selectOpt(val) {
    document.getElementById('opt-consolidated').style.borderColor =
        val === 'consolidated' ? 'var(--navy)' : 'var(--border)';
    document.getElementById('opt-perbatch').style.borderColor =
        val === 'per_batch' ? 'var(--navy)' : 'var(--border)';
}
function submitPayslip() {
    const mode     = document.querySelector('input[name="payslipMode"]:checked').value;
    const entryId  = document.getElementById('payslipEmployee').value;
    const base     = '{{ route("payroll.payslips.generate", $payroll) }}';
    const url      = base + '?mode=' + mode + (entryId ? '&entry_id=' + entryId : '');
    window.open(url, '_blank');
    closePayslipModal();
}
// Close modal on backdrop click
document.getElementById('payslipModal').addEventListener('click', function(e) {
    if (e.target === this) closePayslipModal();
});
</script>
@endsection

{{-- resources/views/special-payroll/differential-show.blade.php --}}
{{--
    Expects from SpecialPayrollController@differentialShow:
      $batch    — SpecialPayrollBatch (type=salary_differential, with employee, approver)
      $employee — Employee model
      $result   — array from SalaryDifferentialService::compute()
--}}

@extends('layouts.app')

@section('title', 'Salary Differential — ' . optional($employee)->last_name)
@section('page-title', 'Special Payroll')

@section('styles')
<style>
/* ── Approval bar ── */
.approval-bar {
    display: flex; align-items: stretch;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); overflow: hidden;
    box-shadow: var(--shadow); margin-bottom: 24px;
}
.approval-step {
    flex: 1; display: flex; align-items: center; gap: 10px;
    padding: 14px 18px; font-size: 0.80rem; font-weight: 600;
    color: var(--text-light); background: var(--surface);
    border-right: 1px solid var(--border); transition: background 0.2s;
}
.approval-step:last-child { border-right: none; }
.approval-step.done          { background: #F1FAF5; color: #1B6B3A; }
.approval-step.active        { background: #EEF1FA; color: var(--navy); }
.approval-step.released-step { background: var(--navy); color: #ffffff; }
.approval-step-dot {
    width: 30px; height: 30px; border-radius: 50%; border: 2px solid currentColor;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; font-weight: 700; flex-shrink: 0;
    background: #ffffff; color: inherit;
}
.approval-step.done .approval-step-dot        { background: #2E7D52; border-color: #2E7D52; color: #fff; }
.approval-step.active .approval-step-dot      { background: var(--navy); border-color: var(--navy); color: #fff; }
.approval-step.released-step .approval-step-dot { background: rgba(255,255,255,.15); border-color: rgba(255,255,255,.6); color: #fff; }
.approval-step-label { line-height: 1.3; min-width: 0; }
.approval-step-label small {
    display: block; font-weight: 400; font-size: 0.70rem;
    opacity: 0.72; margin-top: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── Document header ── */
.doc-header {
    text-align: center; padding: 10px 0 20px;
    border-bottom: 2px solid var(--navy); margin-bottom: 20px;
}
.doc-header .doc-agency { font-size: 0.80rem; color: var(--text-mid); margin: 0 0 2px; }
.doc-header h2 {
    font-size: 0.96rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.04em; color: var(--navy); margin: 0 0 4px;
}
.doc-header .doc-period { font-size: 0.82rem; color: var(--text-mid); margin: 0; }

/* ── Document meta ── */
.doc-meta {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px 24px; margin-bottom: 20px; font-size: 0.85rem;
}
.doc-meta-item { display: flex; flex-direction: column; gap: 2px; }
.doc-meta-item .label {
    font-size: 0.70rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-light);
}
.doc-meta-item .value { font-weight: 600; color: var(--text); }

/* ── Payroll table ── */
.comp-wrap { overflow-x: auto; margin-bottom: 20px; }
.comp-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; white-space: nowrap; }
.comp-table thead tr:first-child th {
    background: var(--navy); color: #fff;
    padding: 7px 10px; text-align: center;
    font-size: 0.72rem; font-weight: 600; letter-spacing: 0.03em;
    border: 1px solid rgba(255,255,255,0.15);
}
.comp-table thead tr:last-child th {
    background: #2a3c6e; color: #cdd6f4;
    padding: 5px 8px; text-align: center;
    font-size: 0.69rem; font-weight: 600;
    border: 1px solid rgba(255,255,255,0.12);
}
.comp-table tbody td {
    padding: 9px 10px; border: 1px solid var(--border); vertical-align: middle;
}
.comp-table tbody td.text-right  { text-align: right; }
.comp-table tbody td.text-center { text-align: center; }
.comp-table tfoot td {
    padding: 9px 10px; font-weight: 700; font-size: 0.82rem;
    background: var(--navy); color: #fff; border: 1px solid rgba(255,255,255,0.15);
}
.comp-table tfoot td.text-right { text-align: right; }
.comp-table tfoot td.gold-text  { color: var(--gold); }
.comp-table tfoot td.green-text { color: #69F0AE; }
.comp-table tfoot td.red-text   { color: #FF8A80; }

/* ── Mobile summary card (replaces scrollable table on small screens) ── */
.mobile-summary {
    display: none;
    border: 1px solid var(--border); border-radius: var(--radius);
    overflow: hidden; margin-bottom: 20px; font-size: 0.84rem;
}
.mobile-summary-header {
    background: var(--navy); color: #fff;
    padding: 10px 14px; font-weight: 700; font-size: 0.80rem;
}
.mobile-summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 9px 14px; border-bottom: 1px solid var(--border);
}
.mobile-summary-row:last-child { border-bottom: none; }
.mobile-summary-row .ms-label {
    font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.04em; color: var(--text-light);
}
.mobile-summary-row .ms-value { font-weight: 600; color: var(--text); }
.mobile-summary-section {
    background: var(--surface-alt, #f8f9ff); padding: 6px 14px;
    font-size: 0.70rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-light);
    border-bottom: 1px solid var(--border);
}

/* ── Cert blocks ── */
.cert-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 28px; }
.cert-block { border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 18px 22px; }
.cert-block-ref {
    display: inline-block; width: 22px; height: 22px; border-radius: 50%;
    background: var(--navy); color: #fff; font-size: 0.75rem; font-weight: 700;
    text-align: center; line-height: 22px; margin-bottom: 6px;
}
.cert-block-title { font-size: 0.78rem; color: var(--text-mid); margin-bottom: 18px; line-height: 1.4; }
.cert-sig-line { border-bottom: 1px solid var(--text-mid); margin-bottom: 6px; height: 24px; width: 80%; }
.cert-sig-name { font-weight: 700; font-size: 0.83rem; }
.cert-sig-role { font-size: 0.73rem; color: var(--text-light); }
.cert-block-meta { font-size: 0.73rem; color: var(--text-mid); margin-top: 10px; }
.cert-block-meta span { display: block; padding: 2px 0; border-bottom: 1px solid var(--border); min-width: 160px; margin-bottom: 4px; }

/* ── Print ── */
@media print {
    .no-print { display: none !important; }
    .approval-bar { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    .cert-grid { page-break-inside: avoid; }
    .mobile-summary { display: none !important; }
    .comp-wrap { display: block !important; }
    .comp-table { font-size: 7.5pt; }
    .doc-header h2 { font-size: 11pt; }
    body { font-size: 9pt; }
    @page { margin: 1.2cm 1cm; size: landscape; }
}

/* ── Mobile overrides ── */
@media (max-width: 768px) {
    .approval-bar { flex-direction: column; }
    .approval-step { border-right: none; border-bottom: 1px solid var(--border); }
    .approval-step:last-child { border-bottom: none; }

    .doc-meta { grid-template-columns: 1fr 1fr; }

    .comp-wrap { display: none; }
    .mobile-summary { display: block; }

    .cert-grid { grid-template-columns: 1fr; }

    .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .page-header .d-flex { width: 100%; }
    .page-header .d-flex .btn { flex: 1; justify-content: center; }
}
</style>
@endsection

@section('content')

@php
    $statusClass = match ($batch->status) {
        'approved' => 'badge-released',
        'released' => 'badge-locked',
        default    => 'badge-draft',
    };
    $statusLabel = match ($batch->status) {
        'draft'    => 'Draft',
        'approved' => 'Approved',
        'released' => 'Released',
        default    => ucfirst($batch->status),
    };

    $fromFmt = $batch->period_start->format('M d, Y');
    $toFmt   = $batch->period_end->format('M d, Y');
    $period  = $fromFmt . ' – ' . $toFmt;

    $canApprove = auth()->user()->hasRole('accountant') && $batch->status === 'draft';
    $canRelease = auth()->user()->hasAnyRole(['ard', 'chief_admin_officer']) && $batch->status === 'approved';

    $spSteps = [
        ['label' => 'HR Prepared',  'sub' => 'Payroll Officer', 'icon' => '✏'],
        ['label' => 'Accountant',   'sub' => 'Certify & Approve',       'icon' => '💼'],
        ['label' => 'RD / ARD',     'sub' => 'Released',                'icon' => '🏛'],
    ];
    $spActiveStep = match ($batch->status) {
        'draft'    => 0,
        'approved' => 1,
        'released' => 2,
        default    => 0,
    };
@endphp

{{-- ═══ PAGE HEADER ═══ --}}
<div class="page-header no-print">
    <div class="page-header-left">
        <h1>Salary Differential</h1>
        <p>
            {{ optional($employee)->last_name }}, {{ optional($employee)->first_name }} ·
            {{ $period }} ·
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('special-payroll.differential.index') }}"
           class="btn btn-outline btn-sm no-print">← All Records</a>
        <button onclick="window.print()" class="btn btn-outline btn-sm no-print">🖨 Print</button>
    </div>
</div>

{{-- ═══ APPROVAL BAR ═══ --}}
<div class="approval-bar no-print">
    @foreach ($spSteps as $idx => $step)
        @php
            $cls = '';
            if ($idx < $spActiveStep)      $cls = 'done';
            elseif ($idx === $spActiveStep) $cls = ($batch->status === 'released') ? 'released-step' : 'active';
        @endphp
        <div class="approval-step {{ $cls }}">
            <div class="approval-step-dot">
                @if ($idx < $spActiveStep) ✓
                @else {{ $step['icon'] }}
                @endif
            </div>
            <div class="approval-step-label">
                {{ $step['label'] }}
                <small>{{ $step['sub'] }}</small>
            </div>
        </div>
    @endforeach
</div>

{{-- ═══ APPROVE / RELEASE FORM ═══ --}}
@if ($canApprove || $canRelease)
<div class="card mb-3 no-print" style="border-left: 4px solid var(--gold);">
    <div class="card-body" style="padding:14px 20px;">
        <form method="POST"
              action="{{ route('special-payroll.differential.approve', $batch->id) }}"
              style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            @csrf
            <div class="form-group" style="flex:1; min-width:220px; margin:0;">
                <label for="approve_remarks" style="margin-bottom:4px;">Remarks (optional)</label>
                <input type="text" id="approve_remarks" name="remarks" placeholder="Optional remarks..." style="width:100%;">
            </div>
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('{{ $canApprove ? 'Approve' : 'Release' }} this salary differential record?')">
                {{ $canApprove ? '✓ Approve' : '✓ Release' }}
            </button>
        </form>
    </div>
</div>
@endif

@if (session('success'))
    <div class="alert alert-success no-print">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error no-print">{{ session('error') }}</div>
@endif

{{-- ═══ PAYROLL DOCUMENT ═══ --}}
<div class="card">
    <div class="card-body">

        {{-- ── Document Header ── --}}
        <div class="doc-header">
            <p class="doc-agency">DEPARTMENT OF LABOR AND EMPLOYMENT — RO9, ZAMBOANGA CITY</p>
            <h2>General Payroll</h2>
            <h2>Salary Differential for Newly Promoted, Step Increment, Salary Adjustment</h2>
            <p class="doc-period">For the Period of {{ strtoupper($period) }}</p>
        </div>

        {{-- ── Meta ── --}}
        <div class="doc-meta">
            <div class="doc-meta-item">
                <span class="label">Employee</span>
                <span class="value">
                    {{ optional($employee)->last_name }},
                    {{ optional($employee)->first_name }}
                    @if (optional($employee)->middle_name)
                        {{ substr($employee->middle_name, 0, 1) }}.
                    @endif
                </span>
            </div>
            <div class="doc-meta-item">
                <span class="label">Position</span>
                <span class="value">{{ optional($employee)->position_title ?? '—' }}</span>
            </div>
            <div class="doc-meta-item">
                <span class="label">Old Rate</span>
                <span class="value">₱{{ number_format($batch->old_basic_salary, 2) }}</span>
            </div>
            <div class="doc-meta-item">
                <span class="label">New Rate</span>
                <span class="value">₱{{ number_format($batch->new_basic_salary, 2) }}</span>
            </div>
            <div class="doc-meta-item">
                <span class="label">Differential</span>
                <span class="value" style="color:var(--navy);">
                    ₱{{ number_format($result['differential'], 2) }} / mo.
                </span>
            </div>
            <div class="doc-meta-item">
                <span class="label">WHT Rate</span>
                <span class="value">{{ number_format($result['wht_rate'] * 100, 0) }}%</span>
            </div>
            <div class="doc-meta-item">
                <span class="label">Status</span>
                <span class="value"><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></span>
            </div>
            @if ($batch->approver)
            <div class="doc-meta-item">
                <span class="label">{{ $batch->status === 'released' ? 'Released by' : 'Approved by' }}</span>
                <span class="value">{{ $batch->approver->name ?? '—' }}</span>
            </div>
            @endif
        </div>

        <p style="font-size:0.78rem; font-style:italic; color:var(--text-mid); margin-bottom:16px;">
            We acknowledge receipt of cash shown opposite our name as full compensation
            for services rendered for the period covered.
        </p>

        {{-- ── Mobile summary card ── --}}
        <div class="mobile-summary">
            <div class="mobile-summary-header">Earned for the Period</div>
            <div class="mobile-summary-row">
                <span class="ms-label">New Rate</span>
                <span class="ms-value">₱{{ number_format($batch->new_basic_salary, 2) }}</span>
            </div>
            <div class="mobile-summary-row">
                <span class="ms-label">Old Rate</span>
                <span class="ms-value">₱{{ number_format($batch->old_basic_salary, 2) }}</span>
            </div>
            <div class="mobile-summary-row">
                <span class="ms-label">Differential / mo.</span>
                <span class="ms-value" style="color:var(--navy); font-weight:700;">₱{{ number_format($result['differential'], 2) }}</span>
            </div>
            @foreach ($result['per_month'] as $mo)
            <div class="mobile-summary-row">
                <span class="ms-label">{{ $mo['month_label'] }} ({{ $mo['days'] }}d)</span>
                <span class="ms-value">₱{{ number_format($mo['earned'], 2) }}</span>
            </div>
            @endforeach
            <div class="mobile-summary-row" style="background:var(--surface-alt, #f0f2fa);">
                <span class="ms-label">Total Earned</span>
                <span class="ms-value" style="color:var(--navy); font-weight:700;">₱{{ number_format($result['total_earned'], 2) }}</span>
            </div>
            <div class="mobile-summary-section">Deductions</div>
            <div class="mobile-summary-row">
                <span class="ms-label">PhilHealth</span>
                <span class="ms-value" style="color:#B71C1C;">₱{{ number_format($result['total_phic'], 2) }}</span>
            </div>
            <div class="mobile-summary-row">
                <span class="ms-label">GSIS Life / Ret.</span>
                <span class="ms-value" style="color:#B71C1C;">₱{{ number_format($result['total_gsis'], 2) }}</span>
            </div>
            <div class="mobile-summary-row">
                <span class="ms-label">Pag-IBIG</span>
                <span class="ms-value" style="color:#B71C1C;">₱{{ number_format($result['total_pagibig'], 2) }}</span>
            </div>
            <div class="mobile-summary-row">
                <span class="ms-label">Withholding Tax</span>
                <span class="ms-value" style="color:#B71C1C;">₱{{ number_format($result['total_wht'], 2) }}</span>
            </div>
            <div class="mobile-summary-row" style="background:var(--surface-alt, #f0f2fa);">
                <span class="ms-label">Total Deductions</span>
                <span class="ms-value" style="color:#B71C1C; font-weight:700;">₱{{ number_format($result['total_deductions'], 2) }}</span>
            </div>
            <div class="mobile-summary-row" style="background:#F1FAF5;">
                <span class="ms-label">Net Amount</span>
                <span class="ms-value" style="color:#1B5E20; font-weight:700; font-size:1rem;">₱{{ number_format($result['net_amount'], 2) }}</span>
            </div>
        </div>

        {{-- ── Desktop payroll table ── --}}
        <div class="comp-wrap">
            <table class="comp-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align:center; vertical-align:middle;">No.</th>
                        <th rowspan="2" style="text-align:left; vertical-align:middle;">Name</th>
                        <th rowspan="2" style="text-align:left; vertical-align:middle;">Position</th>
                        <th rowspan="2" style="text-align:center; vertical-align:middle;">Effectivity Date</th>
                        <th colspan="{{ 3 + count($result['per_month']) }}"
                            style="text-align:center; background:#1e3a8a;">
                            EARNED FOR THE PERIOD
                        </th>
                        <th colspan="5" style="text-align:center; background:#7c1a1a;">DEDUCTIONS</th>
                        <th rowspan="2" style="text-align:right; vertical-align:middle;">NET AMOUNT</th>
                        <th rowspan="2" style="text-align:center; vertical-align:middle; min-width:80px;">SIGNATURE</th>
                    </tr>
                    <tr>
                        <th style="text-align:right;">NEW RATE</th>
                        <th style="text-align:right;">OLD RATE</th>
                        <th style="text-align:right;">DIFFERENTIAL</th>
                        @foreach ($result['per_month'] as $mo)
                            <th style="text-align:right; font-size:0.65rem;">
                                {{ $mo['month_label'] }}<br>
                                <span style="font-weight:400;">({{ $mo['days'] }}d)</span>
                            </th>
                        @endforeach
                        <th style="text-align:right; background:#7c1a1a;">TOTAL</th>
                        <th style="text-align:right; background:#5b2020;">PHILHEALTH</th>
                        <th style="text-align:right; background:#5b2020;">GSIS LIFE/<br>RET</th>
                        <th style="text-align:right; background:#5b2020;">PAG-IBIG</th>
                        <th style="text-align:right; background:#5b2020;">WHLD TAX</th>
                        <th style="text-align:right; background:#7c1a1a;">TOTAL DEDUCT.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td class="fw-bold" style="min-width:180px;">
                            {{ optional($employee)->last_name }},
                            {{ optional($employee)->first_name }}
                            @if (optional($employee)->middle_name)
                                {{ substr($employee->middle_name, 0, 1) }}.
                            @endif
                        </td>
                        <td style="min-width:140px; font-size:0.76rem;">
                            {{ optional($employee)->position_title ?? '—' }}
                        </td>
                        <td class="text-center" style="font-size:0.76rem;">
                            {{ $batch->period_start->format('m/d/Y') }}
                            to<br>
                            {{ $batch->period_end->format('m/d/Y') }}
                        </td>
                        <td class="text-right">{{ number_format($batch->new_basic_salary, 2) }}</td>
                        <td class="text-right">{{ number_format($batch->old_basic_salary, 2) }}</td>
                        <td class="text-right fw-bold">{{ number_format($result['differential'], 2) }}</td>
                        @foreach ($result['per_month'] as $mo)
                            <td class="text-right">{{ number_format($mo['earned'], 2) }}</td>
                        @endforeach
                        <td class="text-right fw-bold">{{ number_format($result['total_earned'], 2) }}</td>
                        <td class="text-right" style="color:#B71C1C;">{{ number_format($result['total_phic'], 2) }}</td>
                        <td class="text-right" style="color:#B71C1C;">{{ number_format($result['total_gsis'], 2) }}</td>
                        <td class="text-right" style="color:#B71C1C;">{{ number_format($result['total_pagibig'], 2) }}</td>
                        <td class="text-right" style="color:#B71C1C;">{{ number_format($result['total_wht'], 2) }}</td>
                        <td class="text-right" style="color:#B71C1C; font-weight:700;">{{ number_format($result['total_deductions'], 2) }}</td>
                        <td class="text-right fw-bold" style="color:#1B5E20;">{{ number_format($result['net_amount'], 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right" style="letter-spacing:0.04em;">TOTAL</td>
                        <td class="text-right">{{ number_format($batch->new_basic_salary, 2) }}</td>
                        <td class="text-right">{{ number_format($batch->old_basic_salary, 2) }}</td>
                        <td class="text-right gold-text">{{ number_format($result['differential'], 2) }}</td>
                        @foreach ($result['per_month'] as $mo)
                            <td class="text-right">{{ number_format($mo['earned'], 2) }}</td>
                        @endforeach
                        <td class="text-right gold-text">{{ number_format($result['total_earned'], 2) }}</td>
                        <td class="text-right red-text">{{ number_format($result['total_phic'], 2) }}</td>
                        <td class="text-right red-text">{{ number_format($result['total_gsis'], 2) }}</td>
                        <td class="text-right red-text">{{ number_format($result['total_pagibig'], 2) }}</td>
                        <td class="text-right red-text">{{ number_format($result['total_wht'], 2) }}</td>
                        <td class="text-right red-text">{{ number_format($result['total_deductions'], 2) }}</td>
                        <td class="text-right green-text">{{ number_format($result['net_amount'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ── Net amount in words ── --}}
        <div style="font-size:0.80rem; margin-bottom:24px;">
            <strong>Net Amount in Words:</strong>
            <em style="color:var(--navy);">
                ₱ {{ number_format($result['net_amount'], 2) }}
            </em>
        </div>

        @if ($batch->remarks)
        <div style="font-size:0.80rem; margin-bottom:24px; padding:10px 14px;
             background:var(--surface-alt, #f8f9ff); border-radius:var(--radius);
             border-left:3px solid var(--navy);">
            <strong>Remarks:</strong> {{ $batch->remarks }}
        </div>
        @endif

        {{-- ── Certification Blocks ── --}}
        <div class="cert-grid">

            <div class="cert-block">
                <div class="cert-block-ref">A</div>
                <div class="cert-block-title">CERTIFIED: Services duly rendered as stated.</div>
                <div class="cert-sig-line"></div>
                <div class="cert-sig-name">NAME</div>
                <div class="cert-sig-role">Position, HRMO / HRMO Designate</div>
                <div class="cert-sig-role">Authorized Official</div>
            </div>

            <div class="cert-block">
                <div class="cert-block-ref">C</div>
                <div class="cert-block-title">
                    APPROVED FOR PAYMENT:
                    <br>
                    <strong>{{ strtoupper(amountToWords($result['net_amount'])) }}</strong>
                    <br>= ₱ {{ number_format($result['net_amount'], 2) }}
                </div>
                <div class="cert-sig-line"></div>
                <div class="cert-sig-name">NAME</div>
                <div class="cert-sig-role">Head of Agency / Authorized Representative</div>
            </div>

            <div class="cert-block">
                <div class="cert-block-ref">B</div>
                <div class="cert-block-title">
                    CERTIFIED: Funds available, cash available, supporting documents complete and proper.
                </div>
                <div class="cert-sig-line"></div>
                <div class="cert-sig-name">NAME</div>
                <div class="cert-sig-role">Accountant</div>
                <div class="cert-block-meta" style="margin-top:14px;">
                    ALOBS NO.: <span></span>
                    Date: <span></span>
                    JEV No.: <span></span>
                    Date: <span></span>
                </div>
            </div>

            <div class="cert-block">
                <div class="cert-block-ref">D</div>
                <div class="cert-block-title">
                    CERTIFIED: Each employee whose name appears above has been paid
                    the amount indicated opposite his/her name.
                </div>
                <div class="cert-sig-line"></div>
                <div class="cert-sig-name">NAME</div>
                <div class="cert-sig-role">Position, Cashier</div>
                <div class="cert-block-meta" style="margin-top:14px;">
                    Date: <span></span>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection

@php
function amountToWords(float $amount): string
{
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
             'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
             'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    $int  = (int) floor($amount);
    $cent = (int) round(($amount - $int) * 100);

    $convert = function (int $n) use (&$convert, $ones, $tens): string {
        if ($n === 0)  return '';
        if ($n < 20)   return $ones[$n];
        if ($n < 100)  return $tens[(int)($n/10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
        if ($n < 1000) return $ones[(int)($n/100)] . ' Hundred' . ($n % 100 ? ' ' . $convert($n % 100) : '');
        if ($n < 1_000_000) {
            return $convert((int)($n/1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : '');
        }
        return $convert((int)($n/1_000_000)) . ' Million' . ($n % 1_000_000 ? ' ' . $convert($n % 1_000_000) : '');
    };

    $words = trim($convert($int)) ?: 'Zero';
    return $words . ' Pesos and ' . str_pad($cent, 2, '0', STR_PAD_LEFT) . '/100 Only';
}
@endphp

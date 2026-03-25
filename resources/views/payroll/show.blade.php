@extends('layouts.app')

@section('title', 'Payroll Batch Detail')
@section('page-title', 'Payroll Batch')

@section('styles')
<style>
/* ── Approval stage bar ──────────────────────────────────── */
.approval-bar {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 24px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}
.approval-step {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    font-size: 0.80rem;
    font-weight: 600;
    color: var(--text-light);
    background: var(--surface);
    position: relative;
    transition: background 0.2s;
    border-right: 1px solid var(--border);
}
.approval-step:last-child { border-right: none; }
.approval-step.done {
    background: var(--success-bg);
    color: var(--success);
}
.approval-step.active {
    background: var(--navy-light);
    color: var(--navy);
}
.approval-step.locked {
    background: var(--navy);
    color: white;
}
.approval-step-dot {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid currentColor;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.70rem;
    flex-shrink: 0;
    background: white;
}
.approval-step.done   .approval-step-dot { background: var(--success); border-color: var(--success); color: white; }
.approval-step.active .approval-step-dot { background: var(--navy); border-color: var(--navy); color: white; }
.approval-step.locked .approval-step-dot { background: white; border-color: white; color: var(--navy); }
.approval-step-label { line-height: 1.2; }
.approval-step-label small { display: block; font-weight: 400; font-size: 0.72rem; opacity: 0.7; margin-top: 1px; }

/* ── Deduction expansion panel ───────────────────────────── */
.ded-toggle {
    background: none;
    border: 1px solid var(--border);
    color: var(--navy);
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 0.73rem;
    cursor: pointer;
    white-space: nowrap;
}
.ded-toggle:hover { background: var(--navy-light); }
.ded-panel {
    display: none;
    background: var(--bg);
    border-top: 1px solid var(--border);
    padding: 10px 14px;
}
.ded-panel.open { display: block; }
.ded-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 4px 16px;
    font-size: 0.76rem;
}
.ded-row {
    display: flex;
    justify-content: space-between;
    padding: 2px 0;
    border-bottom: 1px solid var(--border);
    color: var(--text-mid);
}
.ded-row span:last-child { font-weight: 600; color: var(--text); }

/* ── Summary footer emphasis ─────────────────────────────── */
.tfoot-totals td {
    padding: 12px 14px;
    font-weight: 700;
    font-size: 0.88rem;
}

/* ── Net pay warning ─────────────────────────────────────── */
.net-warn { background: #FFF8E1 !important; }
.net-warn-badge {
    display: inline-block;
    margin-top: 3px;
    font-size: 0.67rem;
    background: #FFE082;
    color: #7A5900;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 700;
    letter-spacing: 0.03em;
}

/* ── Mobile scroll hint ──────────────────────────────────── */
.scroll-hint {
    font-size: 0.75rem;
    color: var(--text-light);
    padding: 6px 14px 0;
}

/* ── Empty state ─────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-light);
}
.empty-state-icon { font-size: 2.5rem; margin-bottom: 12px; }
.empty-state h3   { color: var(--text-mid); margin-bottom: 8px; }
</style>
@endsection

@section('content')

@php
    $months = ['','January','February','March','April','May','June',
               'July','August','September','October','November','December'];
    $periodLabel = ($months[$payroll->period_month] ?? '?') . ' ' .
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
    $isLocked    = $payroll->status === 'locked';
    $isComputed  = !in_array($payroll->status, ['draft']);
    $canCompute  = in_array($payroll->status, ['draft','computed'])
                   && auth()->user()->hasAnyRole(['payroll_officer','hrmo']);
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
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">← All Batches</a>

        {{-- Compute / Re-compute --}}
        @if ($canCompute)
        <form method="POST" action="{{ route('payroll.compute', $payroll) }}"
              onsubmit="return confirm('Run payroll computation for all active employees?\n\nExisting entries will be overwritten.')">
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

        {{-- Payroll Register PDF --}}
        @if ($isComputed)
        <a href="{{ route('reports.payroll-register', ['batch_id' => $payroll->id]) }}"
           class="btn btn-outline btn-sm" target="_blank">
            📄 Payroll Register PDF
        </a>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     APPROVAL STAGE BAR
═══════════════════════════════════════════════════════════════ --}}
@php
    $stages = [
        ['key' => ['draft','computed'], 'label' => 'HR Prepared',   'sub' => 'Payroll Officer / HRMO'],
        ['key' => ['pending_accountant'],         'label' => 'Accountant',     'sub' => 'Certify Funds'],
        ['key' => ['pending_rd'],                 'label' => 'RD / ARD',        'sub' => 'Approval'],
        ['key' => ['released'],                   'label' => 'Released',        'sub' => 'Cashier'],
        ['key' => ['locked'],                     'label' => 'Locked',          'sub' => 'Disbursed'],
    ];

    $stageOrder = ['draft','computed','pending_accountant','pending_rd','released','locked'];
    $currentIdx = array_search($payroll->status, $stageOrder);

    // Map status → stage index
    $statusToStage = [
        'draft'               => 0,
        'computed'            => 0,
        'pending_accountant'  => 1,
        'pending_rd'          => 2,
        'released'            => 3,
        'locked'              => 4,
    ];
    $activeStage = $statusToStage[$payroll->status] ?? 0;
@endphp

<div class="approval-bar">
    @foreach ($stages as $si => $stage)
    @php
        $stageClass = '';
        if ($payroll->status === 'locked') {
            $stageClass = $si === 4 ? 'locked' : 'done';
        } elseif ($si < $activeStage) {
            $stageClass = 'done';
        } elseif ($si === $activeStage) {
            $stageClass = 'active';
        }
    @endphp
    <div class="approval-step {{ $stageClass }}">
        <div class="approval-step-dot">
            @if ($stageClass === 'done') ✓
            @elseif ($stageClass === 'locked') 🔒
            @else {{ $si + 1 }}
            @endif
        </div>
        <div class="approval-step-label">
            {{ $stage['label'] }}
            <small>{{ $stage['sub'] }}</small>
        </div>
    </div>
    @endforeach
</div>

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

{{-- Draft with no entries yet --}}
@if ($payroll->status === 'draft' && $employeeCount === 0)
<div class="alert alert-warning">
    No entries yet. Click <strong>Compute Payroll</strong> above to generate all employee entries.
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PAYROLL REGISTER TABLE
═══════════════════════════════════════════════════════════════ --}}
@if ($employeeCount > 0)
<div class="card">
    <div class="card-header">
        <h3>Payroll Register — {{ $periodLabel }} ({{ $employeeCount }} Employees)</h3>
        <div class="d-flex gap-2 align-center flex-wrap">
            <span class="text-muted" style="font-size:0.78rem;">
                Click <em>Deductions</em> to expand per-employee breakdown.
            </span>
            @if (!$isLocked)
            <span class="text-muted" style="font-size:0.78rem;">
                · Click <em>Payslip</em> to view / print.
            </span>
            @endif
        </div>
    </div>

    <div class="scroll-hint">← Scroll horizontally on mobile</div>

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

                    {{-- Main row --}}
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
                                    id="toggle-{{ $entry->id }}">
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
                        <td>
                            <a href="{{ route('payroll.payslip', [$payroll, $entry]) }}"
                               class="btn btn-outline btn-sm" target="_blank">
                                Payslip
                            </a>
                        </td>
                    </tr>

                    {{-- Deduction expansion panel (spans all columns) --}}
                    @if ($dedCount > 0)
                    <tr class="{{ $netWarn ? 'net-warn' : '' }}" id="ded-row-{{ $entry->id }}" style="display:none;">
                        <td colspan="13" style="padding:0;">
                            <div class="ded-panel" id="ded-panel-{{ $entry->id }}">
                                <div class="ded-grid">
                                    @foreach ($entry->deductions->sortBy(fn($d) => $d->deductionType->display_order ?? 99) as $ded)
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
                               ? '₱' . number_format($payroll->entries->sum('rata'), 2)
                               : '—' }}
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

    {{-- Certification footer --}}
    @if ($isComputed)
    <div class="card-body" style="background:#FAFBFF; border-top:1px solid var(--border); padding:14px 20px;">
        <div class="d-flex gap-2 flex-wrap" style="justify-content:space-between; align-items:flex-end; font-size:0.82rem; color:var(--text-mid);">
            <div>
                <strong>Prepared by:</strong>
                {{ $payroll->creator->name ?? '—' }}
                <span class="text-muted">· {{ $payroll->created_at->format('M d, Y') }}</span>
            </div>
            @if ($payroll->approved_by)
            <div>
                <strong>Approved by:</strong>
                {{ $payroll->approver->name ?? '—' }}
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

{{-- Empty state --}}
<div class="card">
    <div class="card-body empty-state">
        <div class="empty-state-icon">📊</div>
        <h3>No Entries Yet</h3>
        <p>Click <strong>Compute Payroll</strong> above to generate entries for all active employees.</p>
    </div>
</div>

@endif

@endsection

@section('scripts')
<script>
/**
 * Toggle the deduction breakdown panel for a single employee row.
 */
function toggleDed(entryId) {
    const row    = document.getElementById('ded-row-' + entryId);
    const toggle = document.getElementById('toggle-' + entryId);

    if (!row) return;

    const isOpen = row.style.display !== 'none';
    row.style.display = isOpen ? 'none' : 'table-row';
    toggle.textContent = toggle.textContent.replace(isOpen ? '▴' : '▾', isOpen ? '▾' : '▴');
    toggle.textContent = toggle.textContent.includes('▾')
        ? toggle.textContent.replace('▾', '▴')
        : toggle.textContent.replace('▴', '▾');

    // Simpler: just flip the arrow character
    const count = toggle.textContent.replace(/[▾▴]/g, '').trim();
    toggle.textContent = count + ' ' + (isOpen ? '▾' : '▴');
}
</script>
@endsection
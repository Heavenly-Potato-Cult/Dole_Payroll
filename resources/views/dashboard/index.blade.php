@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('styles')
<style>
/* ── Scoped to dashboard only — all use .db- prefix ─────────── */

/* Greeting */
.db-greeting { margin-bottom: 22px; }
.db-greeting h1 { font-size: 1.4rem; margin-bottom: 2px; }
.db-greeting p  { margin: 0; font-size: 0.84rem; color: var(--text-light); }

/* ── Stat grid ───────────────────────────────────────────────── */
.db-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
@media (min-width: 640px) {
    .db-stat-grid { grid-template-columns: repeat(4, 1fr); }
}

.db-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px;
    box-shadow: var(--shadow);
    border-left: 4px solid var(--navy);
    min-width: 0;
}
.db-stat.gold  { border-left-color: var(--gold); }
.db-stat.red   { border-left-color: var(--red); }
.db-stat.green { border-left-color: var(--success); }

.db-stat-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--text-light);
    margin-bottom: 6px;
}
.db-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
    margin-bottom: 4px;
}
.db-stat-value.is-alert { color: var(--red); }
.db-stat-sub {
    font-size: 0.75rem;
    color: var(--text-light);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Main layout: three rows ─────────────────────────────────── */
.db-main { display: flex; flex-direction: column; gap: 18px; margin-bottom: 18px; }

/* ROW 1 & 2: two equal columns */
.db-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
}
@media (min-width: 768px) {
    .db-row { grid-template-columns: 1fr 1fr; }
}

/* ROW 3: chart + quick-actions side by side, height driven by content */
.db-row-bottom {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
    /* height is determined by Quick Actions content — chart fills same height */
}
@media (min-width: 768px) {
    .db-row-bottom {
        grid-template-columns: 1fr 1fr;
        align-items: stretch; /* both cells same height */
    }
}

/* Chart card fills its grid cell completely */
.db-card-chart {
    display: flex;
    flex-direction: column;
    height: 100%;       /* stretch to fill grid row set by Quick Actions */
}
.db-card-chart .db-chart-body {
    flex: 1;
    padding: 12px 16px;
    display: flex;
    align-items: center;   /* vertically center the chart */
    justify-content: center;
}
.db-card-chart .db-chart-wrap {
    position: relative;
    width: 100%;
    height: 100%;          /* fill the flex body */
    max-height: 180px;     /* cap so it never taller than ~4 buttons */
}

/* Quick Actions card — content drives height, no stretching */
.db-card-actions {
    display: flex;
    flex-direction: column;
    height: fit-content; /* shrink to content — this sets the row height */
}

/* ── Cards ───────────────────────────────────────────────────── */
.db-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.db-card-head {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: #FAFBFF;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-shrink: 0;
}
.db-card-head h3 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
    color: var(--navy);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.db-card-body { flex: 1; }

/* ── List rows ───────────────────────────────────────────────── */
.db-list { list-style: none; width: 100%; }
.db-list-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    min-width: 0;
    transition: background 0.1s;
}
.db-list-item:last-child { border-bottom: none; }
.db-list-item:hover { background: var(--navy-light); }

.db-list-main { flex: 1; min-width: 0; }
.db-list-title {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--navy);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.db-list-sub {
    font-size: 0.74rem;
    color: var(--text-light);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
}
.db-list-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}
.db-view-link {
    font-size: 0.72rem;
    color: var(--navy);
    opacity: 0.50;
    text-decoration: none;
    white-space: nowrap;
}
.db-view-link:hover { opacity: 1; text-decoration: underline; }

/* ── Badges ──────────────────────────────────────────────────── */
.db-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 20px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    white-space: nowrap;
}
.db-b-draft    { background: #ECEFF1; color: #607D8B; }
.db-b-computed { background: #E3F2FD; color: #0D47A1; }
.db-b-pending  { background: #FFF9C4; color: #F57F17; }
.db-b-released { background: var(--success-bg); color: var(--success); }
.db-b-locked   { background: var(--navy-light); color: var(--navy); }
.db-b-navy     { background: var(--navy); color: #fff; }
.db-b-gold     { background: var(--gold-light); color: var(--gold-dark); }
.db-b-red      { background: var(--red-light); color: var(--red); }

/* ── Empty states ────────────────────────────────────────────── */
.db-empty {
    padding: 28px 16px;
    text-align: center;
    color: var(--text-light);
    font-size: 0.84rem;
}
.db-empty-icon { font-size: 1.6rem; margin-bottom: 6px; }

/* ── Quick actions ───────────────────────────────────────────── */
.db-actions { padding: 12px 16px 14px; display: flex; flex-direction: column; gap: 8px; }
.db-action-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 6px;
    border: 1.5px solid var(--border);
    background: white;
    color: var(--navy);
    font-size: 0.84rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.12s, border-color 0.12s;
    gap: 8px;
    min-width: 0;
}
.db-action-btn:hover {
    background: var(--navy-light);
    border-color: var(--navy);
    text-decoration: none;
    color: var(--navy);
}
.db-action-btn.primary {
    background: var(--navy);
    color: white;
    border-color: var(--navy);
}
.db-action-btn.primary:hover { background: var(--navy-mid); color: white; }
.db-action-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.db-action-count {
    background: var(--red);
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    flex-shrink: 0;
}

/* ── System info ─────────────────────────────────────────────── */
.db-sysinfo { padding: 16px; }
.db-sysinfo-row {
    display: flex;
    align-items: baseline;
    gap: 12px;
    padding: 5px 0;
    border-bottom: 1px solid var(--border);
    font-size: 0.84rem;
}
.db-sysinfo-row:last-child { border-bottom: none; }
.db-sysinfo-key {
    font-weight: 600;
    color: var(--text-light);
    flex-shrink: 0;
    width: 120px;
    font-size: 0.78rem;
}
.db-sysinfo-val {
    color: var(--text);
    min-width: 0;
    word-break: break-word;
}

/* ── Pulsing dot for pending ─────────────────────────────────── */
.db-pending-dot {
    display: inline-block;
    width: 8px; height: 8px;
    background: var(--red);
    border-radius: 50%;
    margin-left: 6px;
    vertical-align: middle;
    animation: db-pulse 1.8s ease-in-out infinite;
}
@keyframes db-pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: 0.45; transform: scale(0.7); }
}
</style>
@endsection

@section('content')

{{-- ── Greeting ─────────────────────────────────────────────── --}}
<div class="db-greeting">
    <h1>Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 17 ? 'afternoon' : 'evening') }},
        {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
    <p>{{ now()->format('l, F j, Y') }} &mdash; DOLE Regional Office IX, Zamboanga City</p>
</div>

{{-- ── Stat Cards ───────────────────────────────────────────── --}}
<div class="db-stat-grid">
    <div class="db-stat">
        <div class="db-stat-label">Active Employees</div>
        <div class="db-stat-value">{{ number_format($totalEmployees) }}</div>
        <div class="db-stat-sub">Plantilla items</div>
    </div>
    <div class="db-stat gold">
        <div class="db-stat-label">Current Cut-off</div>
        <div class="db-stat-value">{{ $currentCutoff }}</div>
        <div class="db-stat-sub">{{ $currentMonth }} &mdash; {{ now()->day <= 15 ? '1–15' : '16–'.now()->daysInMonth }}</div>
    </div>
    <div class="db-stat red">
        <div class="db-stat-label">Pending Approvals</div>
        <div class="db-stat-value {{ $pendingApprovals > 0 ? 'is-alert' : '' }}">
            {{ $pendingApprovals }}@if($pendingApprovals > 0)<span class="db-pending-dot"></span>@endif
        </div>
        <div class="db-stat-sub">Awaiting your action</div>
    </div>
    <div class="db-stat green">
        <div class="db-stat-label">TEV This Month</div>
        <div class="db-stat-value">{{ $tevThisMonth }}</div>
        <div class="db-stat-sub">{{ $currentMonth }}</div>
    </div>
</div>

{{-- ── Main Layout ──────────────────────────────────────────── --}}
<div class="db-main">

    {{-- ROW 1: Payroll Batches | TEV Requests --}}
    <div class="db-row">

        @role('payroll_officer|hrmo|accountant|ard|cashier|chief_admin_officer')
        <div class="db-card">
            <div class="db-card-head">
                <h3>💰 Recent Payroll Batches</h3>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm" style="flex-shrink:0;">View All</a>
            </div>
            <div class="db-card-body">
                @if($recentPayroll->isEmpty())
                    <div class="db-empty">
                        <div class="db-empty-icon">📭</div>
                        No payroll batches yet.
                    </div>
                @else
                    <ul class="db-list">
                        @foreach($recentPayroll as $batch)
                        @php
                            $mn = \Carbon\Carbon::create()->month($batch->period_month)->format('M');
                            $sm = [
                                'draft'              => ['Draft',        'db-b-draft'],
                                'computed'           => ['Computed',     'db-b-computed'],
                                'pending_accountant' => ['Acct. Review', 'db-b-pending'],
                                'pending_rd'         => ['RD Review',    'db-b-pending'],
                                'released'           => ['Released',     'db-b-released'],
                                'locked'             => ['Locked',       'db-b-locked'],
                            ];
                            $s = $sm[$batch->status] ?? [$batch->status, 'db-b-draft'];
                            $cutLabel = $batch->cutoff === '1st' ? '1st (1–15)' : '2nd (16–end)';
                            $creatorName = $batch->creator ? $batch->creator->name : null;
                        @endphp
                        <li class="db-list-item">
                            <div class="db-list-main">
                                <div class="db-list-title">{{ $mn }} {{ $batch->period_year }}</div>
                                <div class="db-list-sub">
                                    {{ $cutLabel }}@if($creatorName) &mdash; {{ $creatorName }}@endif
                                </div>
                            </div>
                            <div class="db-list-right">
                                <span class="db-badge {{ $s[1] }}">{{ $s[0] }}</span>
                                <a href="{{ route('payroll.show', $batch->id) }}" class="db-view-link">View →</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        @endrole

        @role('payroll_officer|hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer')
        <div class="db-card">
            <div class="db-card-head">
                <h3>✈ Recent TEV Requests</h3>
                <a href="{{ route('tev.index') }}" class="btn btn-outline btn-sm" style="flex-shrink:0;">View All</a>
            </div>
            <div class="db-card-body">
                @if($recentTev->isEmpty())
                    <div class="db-empty">
                        <div class="db-empty-icon">✈</div>
                        No TEV requests yet.
                    </div>
                @else
                    <ul class="db-list">
                        @foreach($recentTev as $tev)
                        @php
                            $tevStatuses = [
                                'submitted'            => ['Submitted',   'db-b-draft'],
                                'hr_approved'          => ['HR Approved', 'db-b-computed'],
                                'accountant_certified' => ['Acct. Cert.', 'db-b-pending'],
                                'rd_approved'          => ['RD Approved', 'db-b-released'],
                                'released'             => ['Released',    'db-b-released'],
                                'cancelled'            => ['Cancelled',   'db-b-red'],
                            ];
                            $t = $tevStatuses[$tev->status] ?? [$tev->status, 'db-b-draft'];
                            $trackBadge = $tev->track === 'cash_advance'
                                ? ['Cash Adv.', 'db-b-navy']
                                : ['Reimburse', 'db-b-gold'];
                            $empName = $tev->employee
                                ? $tev->employee->last_name.', '.substr($tev->employee->first_name, 0, 1).'.'
                                : '—';
                        @endphp
                        <li class="db-list-item">
                            <div class="db-list-main">
                                <div class="db-list-title" style="font-family:monospace;font-size:0.8rem;">
                                    {{ $tev->tev_no }}
                                </div>
                                <div class="db-list-sub">{{ $empName }}</div>
                            </div>
                            <div class="db-list-right">
                                <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
                                    <span class="db-badge {{ $trackBadge[1] }}">{{ $trackBadge[0] }}</span>
                                    <span class="db-badge {{ $t[1] }}">{{ $t[0] }}</span>
                                </div>
                                <a href="{{ route('tev.show', $tev->id) }}" class="db-view-link">View →</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        @endrole

    </div>{{-- /.db-row --}}

    {{-- ROW 2: Payroll Status Chart | Quick Actions
         Quick Actions content = row height. Chart stretches to match. --}}
    @role('payroll_officer|hrmo|accountant|ard|chief_admin_officer')
    @if($recentPayroll->isNotEmpty())
    <div class="db-row-bottom">

        {{-- Chart — stretches to fill whatever height Quick Actions sets --}}
        <div class="db-card db-card-chart">
            <div class="db-card-head">
                <h3>📊 Payroll Status Overview</h3>
            </div>
            <div class="db-chart-body">
                <div class="db-chart-wrap">
                    <canvas id="payrollChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick Actions — height determined purely by its own content --}}
        <div class="db-card db-card-actions">
            <div class="db-card-head">
                <h3>⚡ Quick Actions</h3>
            </div>
            <div class="db-actions">

                @role('payroll_officer|hrmo')
                <a href="{{ route('payroll.create') }}" class="db-action-btn primary">
                    <span class="db-action-left">💰 New Payroll Batch</span><span>→</span>
                </a>
                <a href="{{ route('office-orders.create') }}" class="db-action-btn">
                    <span class="db-action-left">📝 New Office Order</span><span>→</span>
                </a>
                <a href="{{ route('tev.index') }}" class="db-action-btn">
                    <span class="db-action-left">✈ TEV Requests</span><span>→</span>
                </a>
                @endrole

                @role('accountant')
                <a href="{{ route('payroll.index') }}?status=pending_accountant" class="db-action-btn primary">
                    <span class="db-action-left">
                        📋 Pending Payroll Review
                        @if($pendingApprovals > 0)<span class="db-action-count">{{ $pendingApprovals }}</span>@endif
                    </span><span>→</span>
                </a>
                <a href="{{ route('tev.index') }}?status=hr_approved" class="db-action-btn">
                    <span class="db-action-left">✈ Pending TEV Certification</span><span>→</span>
                </a>
                @endrole

                @role('ard|chief_admin_officer')
                <a href="{{ route('payroll.index') }}?status=pending_rd" class="db-action-btn primary">
                    <span class="db-action-left">
                        📋 Pending Payroll Approvals
                        @if($pendingApprovals > 0)<span class="db-action-count">{{ $pendingApprovals }}</span>@endif
                    </span><span>→</span>
                </a>
                <a href="{{ route('tev.index') }}?status=accountant_certified" class="db-action-btn">
                    <span class="db-action-left">✈ Pending TEV Approvals</span><span>→</span>
                </a>
                @endrole

                @role('cashier')
                <a href="{{ route('tev.index') }}?status=rd_approved" class="db-action-btn primary">
                    <span class="db-action-left">
                        💸 TEV for Release
                        @if($pendingApprovals > 0)<span class="db-action-count">{{ $pendingApprovals }}</span>@endif
                    </span><span>→</span>
                </a>
                @endrole

                @role('budget_officer')
                <a href="{{ route('tev.index') }}?status=submitted" class="db-action-btn primary">
                    <span class="db-action-left">
                        📥 TEV Submissions
                        @if($pendingApprovals > 0)<span class="db-action-count">{{ $pendingApprovals }}</span>@endif
                    </span><span>→</span>
                </a>
                @endrole

                @role('payroll_officer|hrmo|accountant|budget_officer|chief_admin_officer')
                <a href="{{ route('reports.index') }}" class="db-action-btn">
                    <span class="db-action-left">📊 Reports</span><span>→</span>
                </a>
                @endrole

            </div>
        </div>

    </div>{{-- /.db-row-bottom --}}
    @endif
    @endrole

    {{-- Quick Actions for roles without chart access --}}
    @role('cashier|budget_officer')
    <div class="db-row">
        <div class="db-card db-card-actions">
            <div class="db-card-head">
                <h3>⚡ Quick Actions</h3>
            </div>
            <div class="db-actions">
                @role('cashier')
                <a href="{{ route('tev.index') }}?status=rd_approved" class="db-action-btn primary">
                    <span class="db-action-left">
                        💸 TEV for Release
                        @if($pendingApprovals > 0)<span class="db-action-count">{{ $pendingApprovals }}</span>@endif
                    </span><span>→</span>
                </a>
                @endrole
                @role('budget_officer')
                <a href="{{ route('tev.index') }}?status=submitted" class="db-action-btn primary">
                    <span class="db-action-left">
                        📥 TEV Submissions
                        @if($pendingApprovals > 0)<span class="db-action-count">{{ $pendingApprovals }}</span>@endif
                    </span><span>→</span>
                </a>
                @endrole
            </div>
        </div>
    </div>
    @endrole

</div>{{-- /.db-main --}}

{{-- ── System Info ──────────────────────────────────────────── --}}
<div class="db-card">
    <div class="db-card-head">
        <h3>⚙ System Information</h3>
    </div>
    <div class="db-sysinfo">
        <div class="db-sysinfo-row">
            <span class="db-sysinfo-key">Laravel</span>
            <span class="db-sysinfo-val">{{ app()->version() }}</span>
        </div>
        <div class="db-sysinfo-row">
            <span class="db-sysinfo-key">PHP</span>
            <span class="db-sysinfo-val">{{ PHP_VERSION }}</span>
        </div>
        <div class="db-sysinfo-row">
            <span class="db-sysinfo-key">Environment</span>
            <span class="db-sysinfo-val">{{ config('app.env') }}</span>
        </div>
        <div class="db-sysinfo-row">
            <span class="db-sysinfo-key">Logged in as</span>
            <span class="db-sysinfo-val">
                {{ auth()->user()->name }} &mdash;
                <span class="role-badge">{{ auth()->user()->getRoleNames()->first() }}</span>
            </span>
        </div>
        <div class="db-sysinfo-row">
            <span class="db-sysinfo-key">Server Time</span>
            <span class="db-sysinfo-val">{{ now()->format('D, d M Y H:i:s T') }}</span>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@role('payroll_officer|hrmo|accountant|ard|chief_admin_officer')
@if($recentPayroll->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var cs     = getComputedStyle(document.documentElement);
    var navy   = cs.getPropertyValue('--navy').trim()   || '#0F1B4C';
    var gold   = cs.getPropertyValue('--gold').trim()   || '#F9A825';
    var red    = cs.getPropertyValue('--red').trim()    || '#B71C1C';
    var green  = '#1B5E20';
    var purple = '#4A148C';
    var gray   = '#9090AA';

    var allLabels = ['Draft','Computed','Pending Accountant','Pending RD','Released','Locked'];
    var allColors = [gray, navy, gold, red, green, purple];
    var rawData   = @json(array_values($payrollStatusData));

    var labels = [], data = [], colors = [];
    for (var i = 0; i < rawData.length; i++) {
        if (rawData[i] > 0) {
            labels.push(allLabels[i]);
            data.push(rawData[i]);
            colors.push(allColors[i]);
        }
    }
    if (!data.length) { labels = ['No Batches']; data = [1]; colors = [gray]; }

    var ctx = document.getElementById('payrollChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 8,
            }]
        },
options: {
    responsive: true,
    maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: { padding: 12, font: { size: 11 }, boxWidth: 11, boxHeight: 11 }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return '  ' + ctx.label + ': ' + ctx.parsed + ' batch(es)';
                        }
                    }
                }
            }
        }
    });
})();
</script>
@endif
@endrole
@endsection
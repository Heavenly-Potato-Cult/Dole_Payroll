@extends('layouts.tev')

@section('title', 'TEV Dashboard')
@section('page-title', 'TEV Dashboard')

@section('styles')
<style>
/* ════════════════════════════════════════════════════════════════
   TEV DASHBOARD — scoped with .td- prefix
   Mobile-first, fully responsive
   ════════════════════════════════════════════════════════════════ */

/* ── Greeting ─────────────────────────────────────────────────── */
.td-greeting {
    margin-bottom: 20px;
    padding: 20px;
    background: linear-gradient(135deg, var(--navy) 0%, #1a2d6d 100%);
    border-radius: var(--radius);
    color: #fff;
    position: relative;
    overflow: hidden;
}
.td-greeting::after {
    content: '';
    position: absolute;
    right: -30px; top: -30px;
    width: 140px; height: 140px;
    background: rgba(249,168,37,0.12);
    border-radius: 50%;
}

.td-greeting-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    gap: 12px;
}

.td-greeting h1 {
    font-size: clamp(1.1rem, 3vw, 1.4rem);
    margin: 0;
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}

.td-greeting .td-role-pill {
    display: inline-block;
    background: rgba(249,168,37,0.22);
    border: 1px solid rgba(249,168,37,0.45);
    color: var(--gold);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    flex-shrink: 0;
}

.td-greeting-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.td-greeting-date {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
    font-weight: 500;
}

.td-greeting-location {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.65);
}

/* Mobile responsiveness */
@media (max-width: 480px) {
    .td-greeting-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .td-greeting .td-role-pill {
        align-self: flex-start;
    }
}

/* ── Stat Grid ────────────────────────────────────────────────── */
.td-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 18px;
}
@media (min-width: 480px) { .td-stat-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 768px) { .td-stat-grid { grid-template-columns: repeat(3, 1fr); } }

.td-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 15px 13px;
    box-shadow: var(--shadow);
    border-top: 3px solid var(--navy);
    min-width: 0;
    position: relative;
    overflow: hidden;
}
.td-stat.gold  { border-top-color: var(--gold); }
.td-stat.red   { border-top-color: var(--red); }
.td-stat.green { border-top-color: var(--success); }
.td-stat.teal  { border-top-color: #00838F; }

.td-stat-icon {
    font-size: 1.4rem;
    margin-bottom: 6px;
    line-height: 1;
    display: block;
}
.td-stat-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--text-light);
    margin-bottom: 4px;
}
.td-stat-value {
    font-size: clamp(1.55rem, 4vw, 1.9rem);
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.td-stat-value.is-alert { color: var(--red); }
.td-stat-sub {
    font-size: 0.72rem;
    color: var(--text-light);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Pulsing dot ─────────────────────────────────────────────── */
.td-dot {
    display: inline-block;
    width: 8px; height: 8px;
    background: var(--red);
    border-radius: 50%;
    flex-shrink: 0;
    animation: tdpulse 1.8s ease-in-out infinite;
}
@keyframes tdpulse {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:0.4; transform:scale(0.65); }
}

/* ── Layout ───────────────────────────────────────────────────── */
.td-main { display: flex; flex-direction: column; gap: 16px; margin-bottom: 16px; }

.td-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 768px) { .td-row { grid-template-columns: 1fr 1fr; } }

/* ── Cards ───────────────────────────────────────────────────── */
.td-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.td-card-head {
    padding: 11px 16px;
    border-bottom: 1px solid var(--border);
    background: #FAFBFF;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-shrink: 0;
}
.td-card-head h3 {
    font-size: 0.88rem;
    font-weight: 600;
    margin: 0;
    color: var(--navy);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.td-card-body { flex: 1; }

/* ── Table ───────────────────────────────────────────────────── */
.td-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.td-table th {
    background: #F5F7FA;
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-light);
    padding: 10px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.td-table td {
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.td-table tr:last-child td { border-bottom: none; }
.td-table tr:hover td { background: var(--navy-light); }

/* ── Badges ──────────────────────────────────────────────────── */
.td-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 20px;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    white-space: nowrap;
}
.td-b-draft    { background: #ECEFF1; color: #607D8B; }
.td-b-computed { background: #E3F2FD; color: #0D47A1; }
.td-b-pending  { background: #FFF9C4; color: #B45309; }
.td-b-released { background: var(--success-bg); color: var(--success); }
.td-b-locked   { background: var(--navy-light); color: var(--navy); }
.td-b-navy     { background: var(--navy); color: #fff; }
.td-b-gold     { background: var(--gold-light); color: var(--gold-dark); }
.td-b-red      { background: var(--red-light); color: var(--red); }
.td-b-teal     { background: #E0F7FA; color: #00838F; }

/* ── Empty states ────────────────────────────────────────────── */
.td-empty {
    padding: 26px 16px;
    text-align: center;
    color: var(--text-light);
    font-size: 0.82rem;
}
.td-empty-icon { font-size: 1.5rem; margin-bottom: 6px; }

/* ── Note card ───────────────────────────────────────────────── */
.td-note-card {
    background: linear-gradient(135deg, #FFF9C4 0%, #FFF59D 100%);
    border: 1px solid #FBC02D;
    border-radius: var(--radius);
    padding: 16px;
    color: #B45309;
}
.td-note-card h3 {
    font-size: 0.88rem;
    font-weight: 600;
    margin: 0 0 6px;
    color: #B45309;
}
.td-note-card p {
    font-size: 0.82rem;
    margin: 0;
    color: #B45309;
}
</style>
@endsection

@section('content')

{{-- ── Greeting ──────────────────────────────────────────────── --}}
<div class="td-greeting">
    <div class="td-greeting-header">
        <h1>Travel & Expense Voucher</h1>
        <span class="td-role-pill">{{ str_replace('_', ' ', strtoupper(auth()->user()->getRoleNames()->first() ?? 'user')) }}</span>
    </div>
    <div class="td-greeting-body">
        <div class="td-greeting-date">{{ now()->format('l, F j, Y') }}</div>
        <div class="td-greeting-location">DOLE Regional Office IX, Zamboanga City</div>
    </div>
</div>

{{-- ── Stat Cards ─────────────────────────────────────────────── --}}
<div class="td-stat-grid">

    {{-- Card 1: Pending Requests --}}
    <div class="td-stat red">
        <span class="td-stat-icon">⏳</span>
        <div class="td-stat-label">Pending Requests</div>
        <div class="td-stat-value {{ $pendingRequests > 0 ? 'is-alert' : '' }}">
            {{ $pendingRequests }}@if($pendingRequests > 0)<span class="td-dot"></span>@endif
        </div>
        <div class="td-stat-sub">Awaiting accountant review</div>
    </div>

    {{-- Card 2: For My Approval --}}
    <div class="td-stat gold">
        <span class="td-stat-icon">✅</span>
        <div class="td-stat-label">For My Approval</div>
        <div class="td-stat-value {{ $pendingMyApproval > 0 ? 'is-alert' : '' }}">
            {{ $pendingMyApproval }}@if($pendingMyApproval > 0)<span class="td-dot"></span>@endif
        </div>
        <div class="td-stat-sub">Requires your action</div>
    </div>

    {{-- Card 3: My Requests This Month --}}
    <div class="td-stat green">
        <span class="td-stat-icon">📝</span>
        <div class="td-stat-label">My Requests This Month</div>
        <div class="td-stat-value">{{ $myRequestsThisMonth }}</div>
        <div class="td-stat-sub">{{ now()->format('F Y') }}</div>
    </div>

</div>{{-- /.td-stat-grid --}}

{{-- ══════════════════════════════════════════════════════════════
     MAIN CONTENT
     ══════════════════════════════════════════════════════════════ --}}
<div class="td-main">

{{-- ── Recent TEV Requests ───────────────────────────────────── --}}
<div class="td-card">
    <div class="td-card-head">
        <h3>✈ Recent TEV Requests</h3>
        <a href="{{ route('tev.requests.index') }}" class="btn btn-outline btn-sm" style="flex-shrink:0;">View All</a>
    </div>
    <div class="td-card-body">
        @if($recentRequests->isEmpty())
            <div class="td-empty"><div class="td-empty-icon">✈</div>No TEV requests yet.</div>
        @else
            <table class="td-table">
                <thead>
                    <tr>
                        <th>Request No.</th>
                        <th>Employee</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRequests as $tev)
                    @php
                        $tevSt = ['draft'=>['Draft','td-b-draft'],'submitted'=>['Submitted','td-b-pending'],'accountant_certified'=>['Acct. Cert.','td-b-computed'],'rd_approved'=>['RD Approved','td-b-released'],'cashier_released'=>['CA Released','td-b-gold'],'reimbursed'=>['Reimbursed','td-b-released'],'liquidation_filed'=>['Liq. Filed','td-b-pending'],'liquidated'=>['Liquidated','td-b-locked'],'rejected'=>['Rejected','td-b-red']];
                        $t  = $tevSt[$tev->status] ?? [ucwords(str_replace('_',' ',$tev->status)),'td-b-draft'];
                        $en = $tev->employee ? $tev->employee->last_name.', '.substr($tev->employee->first_name,0,1).'.' : '—';
                    @endphp
                    <tr>
                        <td style="font-family:monospace;font-weight:600;">{{ $tev->tev_no }}</td>
                        <td>{{ $en }}</td>
                        <td>{{ $tev->destination }}</td>
                        <td><span class="td-badge {{ $t[1] }}">{{ $t[0] }}</span></td>
                        <td style="color:var(--text-light);font-size:0.75rem;">{{ $tev->created_at->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- ── Liquidation Monitor (placeholder) ─────────────────────── --}}
<div class="td-row">
    <div class="td-card" style="grid-column: 1 / -1;">
        <div class="td-card-body">
            <div class="td-note-card">
                <h3>💡 Liquidation Monitor</h3>
                <p>Track cash advance liquidations and outstanding balances. Coming soon.</p>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.td-main --}}

@endsection

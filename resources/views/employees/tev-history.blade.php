{{-- resources/views/employees/tev-history.blade.php --}}
{{--
    Expects from ReportController@employeeTevHistory:
      $emp         — Employee with division
      $tevRequests — paginated TevRequest (with officeOrder), most recent first
--}}

@extends('layouts.app')

@section('title', 'TEV History — ' . $emp->last_name . ', ' . $emp->first_name)
@section('page-title', 'Employees')

@section('styles')
<style>
/* ── Employee meta grid ── */
.emp-meta-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px 20px;
}
.emp-meta-item { display: flex; flex-direction: column; gap: 2px; }
.emp-meta-item .label {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-light);
}
.emp-meta-item .value { font-weight: 600; font-size: 0.88rem; color: var(--text); }

/* ── Desktop table (≥ 769px) ── */
.sd-detail-row { display: none !important; }
.sd-expand-btn { display: none !important; }

@media (min-width: 769px) {
    .sd-table              { display: table; width: 100%; border-collapse: collapse; }
    .sd-table thead        { display: table-header-group; }
    .sd-table tbody        { display: table-row-group; }
    .sd-table tr           { display: table-row; }
    .sd-table th,
    .sd-table td           { display: table-cell; }
}

.sd-table thead th {
    background: var(--navy); color: #fff;
    padding: 9px 12px; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.04em;
    border: 1px solid rgba(255,255,255,0.12);
}
.sd-table tbody tr { transition: background .12s; }
.sd-table tbody tr:hover { background: #eef0fb; }
.sd-table tbody td { padding: 9px 12px; border: 1px solid var(--border); vertical-align: middle; font-size: 0.82rem; }
.sd-table .text-right  { text-align: right; }
.sd-table .text-center { text-align: center; }

/* ── Mobile (≤ 768px) ── */
@media (max-width: 768px) {

    .emp-meta-grid { grid-template-columns: 1fr 1fr; }

    .table-wrap { overflow: visible; }

    .sd-table        { display: block; }
    .sd-table thead  { display: none; }
    .sd-table tbody  { display: block; }

    /* Card-style main row */
    .sd-table tr.sd-main-row {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background .15s;
        min-height: 64px;
    }
    .sd-table tr.sd-main-row:active { background: var(--bg); }

    /* Hide these on mobile — shown in detail panel */
    .sd-table tr.sd-main-row td.col-track,
    .sd-table tr.sd-main-row td.col-dates,
    .sd-table tr.sd-main-row td.col-purpose,
    .sd-table tr.sd-main-row td.col-total,
    .sd-table tr.sd-main-row td.col-actions { display: none; }

    /* TEV No — fixed left */
    .sd-table tr.sd-main-row td.col-tev {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        padding: 0 10px 0 0;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--navy);
        white-space: nowrap;
    }

    /* Status — fills remaining space */
    .sd-table tr.sd-main-row td.col-status {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 0;
    }

    /* Expand chevron */
    .sd-expand-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 26px; height: 26px;
        flex-shrink: 0;
        border-radius: 50%;
        background: transparent;
        border: 1.5px solid var(--border);
        cursor: pointer;
        font-size: 0.65rem;
        color: var(--text-mid);
        transition: transform .2s, background .15s, border-color .15s;
        margin-left: 4px;
    }
    .sd-main-row.open .sd-expand-btn {
        transform: rotate(180deg);
        background: var(--navy-light, #e8ecf4);
        border-color: var(--navy);
        color: var(--navy);
    }

    /* Expanded detail panel */
    tr.sd-detail-row.open {
        display: block !important;
        border-bottom: 1px solid var(--border);
        background: var(--bg, #f8f9fb);
    }
    tr.sd-detail-row.open td {
        display: block;
        padding: 12px 16px 16px;
    }

    /* Detail grid */
    .sd-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 20px;
        margin-bottom: 14px;
    }
    .sd-detail-item label {
        display: block;
        font-size: 0.68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em;
        color: var(--text-light); margin-bottom: 2px;
    }
    .sd-detail-item span { font-size: 0.84rem; font-weight: 500; color: var(--text); }

    .sd-detail-actions { display: flex; gap: 8px; margin-top: 4px; }
}

@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    body { font-size: 9pt; }
    @page { margin: 1.2cm 1cm; }
}
</style>
@endsection

@section('content')

<div class="page-header no-print">
    <div class="page-header-left">
        <h1>TEV History</h1>
        <p class="text-muted">
            {{ $emp->last_name }}, {{ $emp->first_name }}
            @if ($emp->middle_name) {{ $emp->middle_name }} @endif
            &mdash; {{ optional($emp->division)->name ?? 'No Division' }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('employees.show', $emp->id) }}" class="btn btn-outline btn-sm">← Back to Employee</a>
        <a href="{{ route('reports.tev-register', ['employee_id' => $emp->id]) }}"
           class="btn btn-outline btn-sm">Register View</a>
    </div>
</div>

{{-- ── Employee summary card ── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h3>👤 Employee Information</h3></div>
    <div class="card-body">
        <div class="emp-meta-grid">
            <div class="emp-meta-item">
                <span class="label">Plantilla Item No.</span>
                <span class="value" style="font-family:monospace;">{{ $emp->plantilla_item_no ?? '—' }}</span>
            </div>
            <div class="emp-meta-item">
                <span class="label">Full Name</span>
                <span class="value">
                    {{ $emp->last_name }}, {{ $emp->first_name }}
                    @if ($emp->middle_name) {{ substr($emp->middle_name, 0, 1) }}. @endif
                </span>
            </div>
            <div class="emp-meta-item">
                <span class="label">Position</span>
                <span class="value">{{ $emp->position_title ?? '—' }}</span>
            </div>
            <div class="emp-meta-item">
                <span class="label">Division</span>
                <span class="value">{{ optional($emp->division)->name ?? '—' }}</span>
            </div>
            <div class="emp-meta-item">
                <span class="label">Total TEV Records</span>
                <span class="value">{{ $tevRequests->total() }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── TEV history table ── --}}
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Travel Expense Vouchers
            <span style="font-size:0.75rem; font-weight:400; color:var(--text-mid);">
                ({{ $tevRequests->total() }} total)
            </span>
        </h3>
        <button class="btn btn-outline btn-sm no-print" onclick="window.print()">🖨 Print</button>
    </div>

    <div class="card-body" style="padding:0;">
        <table class="sd-table">
            <thead>
                <tr>
                    <th>TEV No.</th>
                    <th>Track</th>
                    <th>Travel Dates</th>
                    <th>Purpose</th>
                    <th class="text-right">Grand Total</th>
                    <th>Status</th>
                    <th class="text-center no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tevRequests as $tev)
                    @php
                        $trackLabel = $tev->track === 'cash_advance' ? 'Cash Advance' : 'Reimbursement';
                        $trackStyle = $tev->track === 'cash_advance'
                            ? 'background:#E8F5E9; color:#1B5E20; border:1px solid #43A047;'
                            : 'background:#E8EAF6; color:#1A237E; border:1px solid #3949AB;';

                        $statusClass = match ($tev->status) {
                            'submitted'            => 'badge-pending',
                            'hr_approved'          => 'badge-computed',
                            'accountant_certified' => 'badge-computed',
                            'rd_approved'          => 'badge-released',
                            'cashier_released'     => 'badge-locked',
                            'reimbursed'           => 'badge-locked',
                            'rejected'             => 'badge-inactive',
                            default                => 'badge-draft',
                        };
                        $statusLabel = ucwords(str_replace('_', ' ', $tev->status));
                        $hasPdf = in_array($tev->status, ['rd_approved', 'cashier_released', 'reimbursed']);
                    @endphp

                    {{-- ── Main visible row ── --}}
                    <tr class="sd-main-row" data-id="{{ $tev->id }}" onclick="toggleSdRow(this)">

                        <td class="col-tev fw-bold" style="color:var(--navy); white-space:nowrap;">
                            {{ $tev->tev_no }}
                        </td>

                        <td class="col-track">
                            <span style="font-size:0.72rem; font-weight:700; padding:3px 8px;
                                         border-radius:12px; {{ $trackStyle }}">
                                {{ $trackLabel }}
                            </span>
                        </td>

                        <td class="col-dates" style="white-space:nowrap;">
                            {{ $tev->travel_date_start->format('M d') }}
                            –
                            {{ $tev->travel_date_end->format('M d, Y') }}
                        </td>

                        <td class="col-purpose"
                            style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                            title="{{ $tev->purpose }}">
                            {{ $tev->purpose }}
                        </td>

                        <td class="col-total text-right fw-bold">
                            ₱{{ number_format($tev->grand_total, 2) }}
                        </td>

                        <td class="col-status">
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>

                        <td class="col-actions text-center no-print">
                            <div class="d-flex gap-2" style="justify-content:center;">
                                <a href="{{ route('tev.show', $tev->id) }}"
                                   class="btn btn-outline btn-sm"
                                   onclick="event.stopPropagation();">View</a>
                                @if ($hasPdf)
                                    <a href="{{ route('reports.tev-itinerary', $tev->id) }}"
                                       target="_blank"
                                       class="btn btn-outline btn-sm"
                                       title="Itinerary PDF"
                                       onclick="event.stopPropagation();">📄</a>
                                @endif
                            </div>
                            {{-- Mobile expand chevron --}}
                            <span class="sd-expand-btn" aria-label="Expand">▼</span>
                        </td>

                    </tr>

                    {{-- ── Expandable detail row (mobile only) ── --}}
                    <tr class="sd-detail-row" id="sd-detail-{{ $tev->id }}">
                        <td colspan="7">
                            <div class="sd-detail-grid">
                                <div class="sd-detail-item">
                                    <label>Track</label>
                                    <span>
                                        <span style="font-size:0.72rem; font-weight:700;
                                                     padding:2px 8px; border-radius:10px;
                                                     {{ $trackStyle }}">{{ $trackLabel }}</span>
                                    </span>
                                </div>
                                <div class="sd-detail-item">
                                    <label>Travel Dates</label>
                                    <span>
                                        {{ $tev->travel_date_start->format('M d') }}
                                        – {{ $tev->travel_date_end->format('M d, Y') }}
                                    </span>
                                </div>
                                <div class="sd-detail-item" style="grid-column:1/-1;">
                                    <label>Purpose</label>
                                    <span>{{ $tev->purpose }}</span>
                                </div>
                                <div class="sd-detail-item">
                                    <label>Grand Total</label>
                                    <span class="fw-bold" style="color:var(--navy);">
                                        ₱{{ number_format($tev->grand_total, 2) }}
                                    </span>
                                </div>
                                <div class="sd-detail-item">
                                    <label>Status</label>
                                    <span><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></span>
                                </div>
                            </div>
                            <div class="sd-detail-actions">
                                <a href="{{ route('tev.show', $tev->id) }}"
                                   class="btn btn-outline btn-sm">View</a>
                                @if ($hasPdf)
                                    <a href="{{ route('reports.tev-itinerary', $tev->id) }}"
                                       target="_blank"
                                       class="btn btn-outline btn-sm">📄 Itinerary PDF</a>
                                @endif
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7"
                            style="text-align:center; padding:40px; color:var(--text-light);">
                            No TEV requests found for this employee.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:12px;" class="no-print">
    {{ $tevRequests->links() }}
</div>

@endsection

@section('scripts')
<script>
function toggleSdRow(mainRow) {
    if (window.innerWidth > 768) return;

    const id     = mainRow.dataset.id;
    const detail = document.getElementById('sd-detail-' + id);
    const isOpen = mainRow.classList.contains('open');

    document.querySelectorAll('.sd-main-row.open').forEach(r => r.classList.remove('open'));
    document.querySelectorAll('.sd-detail-row.open').forEach(r => r.classList.remove('open'));

    if (!isOpen) {
        mainRow.classList.add('open');
        detail.classList.add('open');
    }
}
</script>
@endsection
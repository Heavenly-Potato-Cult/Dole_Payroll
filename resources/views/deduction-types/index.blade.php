@extends('layouts.app')

@section('title', 'Deduction Types')
@section('page-title', 'Deduction Types')

@section('styles')
<style>
/* ── Layout ──────────────────────────────────────────────────────── */
.dt-header-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

/* ── Category section ────────────────────────────────────────────── */
.dt-category { margin-bottom: 28px; }
.dt-category-label {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-mid);
    padding: 0 4px 6px;
    border-bottom: 2px solid var(--border);
    margin-bottom: 0;
}

/* ── Table ───────────────────────────────────────────────────────── */
.dt-table { width: 100%; border-collapse: collapse; }
.dt-table th {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-mid);
    padding: 8px 14px;
    text-align: left;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.dt-table td {
    padding: 11px 14px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.875rem;
}
.dt-table tr:last-child td { border-bottom: none; }
.dt-table tr:hover td { background: var(--bg); }

/* Inactive row */
.dt-table tr.dt-inactive td { opacity: 0.5; }

/* ── Badges ──────────────────────────────────────────────────────── */
.badge-computed {
    background: #eef2ff; color: #4338ca;
    font-size: 0.63rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    padding: 2px 7px; border-radius: 99px;
    border: 1px solid #c7d2fe;
    white-space: nowrap;
}
.badge-manual {
    background: var(--bg); color: var(--text-mid);
    font-size: 0.63rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    padding: 2px 7px; border-radius: 99px;
    border: 1px solid var(--border);
    white-space: nowrap;
}
.badge-active   { background: #dcfce7; color: #166534; font-size:0.68rem; padding:2px 8px; border-radius:99px; font-weight:700; }
.badge-inactive { background: #fee2e2; color: #991b1b; font-size:0.68rem; padding:2px 8px; border-radius:99px; font-weight:700; }

/* ── Code chip ───────────────────────────────────────────────────── */
.code-chip {
    font-family: monospace;
    font-size: 0.78rem;
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 2px 7px;
    border-radius: 4px;
    color: var(--navy);
    white-space: nowrap;
}

/* ── Order number ────────────────────────────────────────────────── */
.dt-order {
    display: inline-block;
    min-width: 26px;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-light);
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 1px 5px;
}

/* ── Action buttons ──────────────────────────────────────────────── */
.dt-actions { display: flex; gap: 6px; align-items: center; }
.btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--card-bg, #fff);
    cursor: pointer;
    font-size: 0.85rem;
    color: var(--text-mid);
    transition: background .15s, border-color .15s, color .15s;
    text-decoration: none;
}
.btn-icon:hover { background: var(--navy); color: #fff; border-color: var(--navy); }
.btn-icon.danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }

/* ── Notes truncation ────────────────────────────────────────────── */
.dt-notes {
    max-width: 260px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--text-light);
    font-size: 0.78rem;
}

/* ── Summary stats ───────────────────────────────────────────────── */
.dt-stats { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
.dt-stat {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px 20px;
    display: flex; flex-direction: column; gap: 2px;
}
.dt-stat-num { font-size: 1.5rem; font-weight: 800; color: var(--navy); line-height: 1; }
.dt-stat-lbl { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-mid); }

/* ── Computed-lock tooltip ───────────────────────────────────────── */
.lock-tip { position: relative; display: inline-block; }
.lock-tip .tip-text {
    visibility: hidden; opacity: 0;
    width: 220px;
    background: var(--navy);
    color: #fff;
    font-size: 0.72rem;
    border-radius: 6px;
    padding: 6px 10px;
    position: absolute;
    bottom: 130%; left: 50%;
    transform: translateX(-50%);
    transition: opacity .2s;
    pointer-events: none;
    z-index: 10;
    line-height: 1.4;
}
.lock-tip:hover .tip-text { visibility: visible; opacity: 1; }

@media (max-width: 768px) {
    .dt-notes { max-width: 120px; }
    .dt-table th.dt-col-notes,
    .dt-table td.dt-col-notes { display: none; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Deduction Types</h1>
        <p>Manage all deduction and loan types used across payroll and employee enrollments.</p>
    </div>
    <div class="dt-header-actions">
        <a href="{{ route('deduction-types.create') }}" class="btn btn-primary">+ New Deduction Type</a>
    </div>
</div>

{{-- Summary stats --}}
@php
    $allTypes   = $grouped->flatten();
    $totalCount = $allTypes->count();
    $activeCount    = $allTypes->where('is_active', true)->count();
    $computedCount  = $allTypes->where('is_computed', true)->count();
    $inactiveCount  = $allTypes->where('is_active', false)->count();
@endphp

<div class="dt-stats">
    <div class="dt-stat">
        <span class="dt-stat-num">{{ $totalCount }}</span>
        <span class="dt-stat-lbl">Total Types</span>
    </div>
    <div class="dt-stat">
        <span class="dt-stat-num" style="color:#166534;">{{ $activeCount }}</span>
        <span class="dt-stat-lbl">Active</span>
    </div>
    <div class="dt-stat">
        <span class="dt-stat-num" style="color:#4338ca;">{{ $computedCount }}</span>
        <span class="dt-stat-lbl">Auto-Computed</span>
    </div>
    @if ($inactiveCount > 0)
    <div class="dt-stat">
        <span class="dt-stat-num" style="color:#991b1b;">{{ $inactiveCount }}</span>
        <span class="dt-stat-lbl">Inactive</span>
    </div>
    @endif
</div>

@if ($grouped->isEmpty())
    <div class="card">
        <div class="card-body" style="text-align:center;padding:48px;color:var(--text-light);">
            <div style="font-size:2rem;margin-bottom:12px;">📋</div>
            <p>No deduction types found. <a href="{{ route('deduction-types.create') }}">Create the first one</a> or run the seeder.</p>
        </div>
    </div>
@endif

@foreach ($categoryLabels as $catKey => $catLabel)
    @if (isset($grouped[$catKey]))
    <div class="dt-category">
        <div class="dt-category-label">{{ $catLabel }}</div>
        <div class="card" style="overflow:hidden;margin-top:0;border-top-left-radius:0;border-top-right-radius:0;">
            <table class="dt-table">
                <thead>
                    <tr>
                        <th style="width:42px;">#</th>
                        <th style="width:160px;">Code</th>
                        <th>Name</th>
                        <th style="width:110px;">Type</th>
                        <th style="width:80px;">Status</th>
                        <th class="dt-col-notes">Notes</th>
                        <th style="width:100px;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grouped[$catKey]->sortBy('display_order') as $type)
                    <tr class="{{ $type->is_active ? '' : 'dt-inactive' }}">

                        {{-- Order --}}
                        <td><span class="dt-order">{{ $type->display_order }}</span></td>

                        {{-- Code (immutable) --}}
                        <td><span class="code-chip">{{ $type->code }}</span></td>

                        {{-- Name --}}
                        <td>
                            <span style="font-weight:600;color:var(--navy);">{{ $type->name }}</span>
                        </td>

                        {{-- Type badge --}}
                        <td>
                            @if ($type->is_computed)
                                <span class="badge-computed">🔒 Auto-computed</span>
                            @else
                                <span class="badge-manual">Manual</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            @if ($type->is_active)
                                <span class="badge-active">Active</span>
                            @else
                                <span class="badge-inactive">Inactive</span>
                            @endif
                        </td>

                        {{-- Notes --}}
                        <td class="dt-col-notes">
                            <span class="dt-notes" title="{{ $type->notes }}">
                                {{ $type->notes ?: '—' }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="dt-actions" style="justify-content:flex-end;">
                                {{-- Edit --}}
                                <a href="{{ route('deduction-types.edit', $type) }}"
                                   class="btn-icon" title="Edit">✎</a>

                                {{-- Toggle active --}}
                                <form method="POST"
                                      action="{{ route('deduction-types.toggle', $type) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('{{ $type->is_active ? 'Deactivate' : 'Activate' }} this deduction type?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn-icon {{ $type->is_active ? 'danger' : '' }}"
                                            title="{{ $type->is_active ? 'Deactivate' : 'Activate' }}">
                                        {{ $type->is_active ? '⊘' : '✓' }}
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endforeach

{{-- Legend --}}
<div class="card" style="margin-top:8px;">
    <div class="card-body" style="display:flex;gap:24px;flex-wrap:wrap;align-items:center;padding:14px 20px;">
        <span style="font-size:0.75rem;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:.07em;">Legend:</span>
        <span style="font-size:0.78rem;color:var(--text-mid);">
            <span class="badge-computed">🔒 Auto-computed</span>
            &nbsp;— Amount is calculated by the payroll engine (GSIS, PhilHealth, Pag-IBIG, WHT). Cannot be manually enrolled.
        </span>
        <span style="font-size:0.78rem;color:var(--text-mid);">
            <span class="badge-manual">Manual</span>
            &nbsp;— Amount is set per employee via the Deductions enrollment form.
        </span>
        <span style="font-size:0.78rem;color:var(--text-mid);">
            <span class="code-chip">CODE</span>
            &nbsp;— Code is permanent and cannot be changed after creation.
        </span>
    </div>
</div>

@endsection

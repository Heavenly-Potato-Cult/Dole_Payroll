{{-- views/employees/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('styles')
<style>
/* ─────────────────────────────────────────────────────
   FILTER FORM — buttons match input/select height
───────────────────────────────────────────────────── */
.filter-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;   /* all children bottom-aligned */
    flex-wrap: wrap;
}
.filter-form .ff-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.filter-form .ff-group label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--text-mid);
    line-height: 1;
    margin: 0;
}
.filter-form input,
.filter-form select {
    height: 38px;
    margin-bottom: 0 !important;
    box-sizing: border-box;
}
/* Button group: NO label above, so it just aligns to flex-end naturally */
.filter-form .ff-btns {
    display: flex;
    gap: 8px;
    align-items: center;
    /* height matches inputs so flex-end works perfectly */
    height: 38px;
}
.filter-form .ff-btns .btn,
.filter-form .ff-btns .btn-sm {
    height: 38px;
    padding-top: 0;
    padding-bottom: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    white-space: nowrap;
}

/* ─────────────────────────────────────────────────────
   RESPONSIVE TABLE
───────────────────────────────────────────────────── */

/* Detail rows + expand button: always hidden unless mobile overrides */
.emp-detail-row { display: none !important; }
.emp-expand-btn { display: none !important; }

/* ── DESKTOP (≥ 769px): pure normal table, nothing changes ── */
@media (min-width: 769px) {
    .emp-table              { display: table; width: 100%; border-collapse: collapse; }
    .emp-table thead        { display: table-header-group; }
    .emp-table tbody        { display: table-row-group; }
    .emp-table tr           { display: table-row; }
    .emp-table th,
    .emp-table td           { display: table-cell; }
}

/* ── MOBILE (≤ 768px): card rows ── */
@media (max-width: 768px) {

    /* Filter form: stack vertically on small screens */
    .filter-form              { flex-direction: column; align-items: stretch; }
    .filter-form .ff-group,
    .filter-form .ff-btns     { width: 100%; }
    .filter-form .ff-btns     { height: auto; }
    .filter-form .ff-btns .btn,
    .filter-form .ff-btns .btn-sm { flex: 1; }

    /* Kill horizontal scroll */
    .table-wrap { overflow: visible; }

    /* Table becomes a block list */
    .emp-table        { display: block; }
    .emp-table thead  { display: none; }
    .emp-table tbody  { display: block; }

    /* Each data row = card row */
    .emp-table tr.emp-main-row {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background .15s;
        min-height: 64px;
    }
    .emp-table tr.emp-main-row:active { background: var(--bg); }

    /* Hide columns that live in the expanded detail panel */
    .emp-table tr.emp-main-row td.col-plantilla,
    .emp-table tr.emp-main-row td.col-position,
    .emp-table tr.emp-main-row td.col-sg,
    .emp-table tr.emp-main-row td.col-step,
    .emp-table tr.emp-main-row td.col-salary,
    .emp-table tr.emp-main-row td.col-actions {
        display: none;
    }

    /* Name — takes all remaining space */
    .emp-table tr.emp-main-row td.col-name {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;          /* allow text truncation */
        padding: 0;
    }
    .emp-table tr.emp-main-row td.col-name a {
        font-weight: 700;
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Division badge — fixed width, centred */
    .emp-table tr.emp-main-row td.col-division {
        flex: 0 0 auto;
        padding: 0 10px;
        display: flex;
        align-items: center;
    }

    /* Status badge — fixed width, right-aligned */
    .emp-table tr.emp-main-row td.col-status {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    /* Expand chevron button — show on mobile */
    .emp-expand-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        border-radius: 50%;
        background: transparent;
        border: 1.5px solid var(--border);
        cursor: pointer;
        font-size: 0.65rem;
        color: var(--text-mid);
        transition: transform .2s, background .15s, border-color .15s;
        margin-left: 10px;   /* gap from status badge */
    }
    .emp-main-row.open .emp-expand-btn {
        transform: rotate(180deg);
        background: var(--navy-light, #e8ecf4);
        border-color: var(--navy);
        color: var(--navy);
    }

    /* ── Expanded detail panel ── */
    tr.emp-detail-row.open {
        display: block !important;
        border-bottom: 1px solid var(--border);
        background: var(--bg, #f8f9fb);
    }
    tr.emp-detail-row.open td {
        display: block;
        padding: 12px 16px 16px;
    }
    .emp-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 20px;
        margin-bottom: 14px;
    }
    .emp-detail-item label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-light);
        margin-bottom: 3px;
    }
    .emp-detail-item span {
        font-size: 0.85rem;
        color: var(--text);
        font-weight: 500;
    }
    .emp-detail-actions {
        display: flex;
        gap: 8px;
    }
    .emp-detail-actions .btn,
    .emp-detail-actions button {
        flex: 1;
        justify-content: center;
        text-align: center;
    }
}
</style>
@endsection

@section('content')
<?php \Log::info('VIEW START: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Employees</h1>
        <p>DOLE RO9 Regular Plantilla — {{ $employees->total() }} {{ Str::plural('record', $employees->total()) }}</p>
    </div>
    @role('payroll_officer|hrmo')
    <a href="{{ route('employees.create') }}" class="btn btn-primary">+ New Employee</a>
    @endrole
</div>

{{-- ── Filters ───────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('employees.index') }}"
              class="filter-form">

            <div class="ff-group" style="flex:1;min-width:200px;">
                <label>Search</label>
                <input type="text" name="search"
                       value="{{ $search }}"
                       placeholder="Name, plantilla no., position…">
            </div>

            <div class="ff-group" style="min-width:180px;">
                <label>Division</label>
                <select name="division_id">
                    <option value="">All Divisions</option>
                    @foreach ($divisions as $div)
                        <option value="{{ $div->id }}"
                            {{ $divisionId == $div->id ? 'selected' : '' }}>
                            {{ $div->code }} — {{ $div->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ff-group" style="min-width:130px;">
                <label>Status</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="vacant"   {{ $status === 'vacant'   ? 'selected' : '' }}>Vacant</option>
                </select>
            </div>

            {{-- Buttons: no label wrapper, so they naturally align to flex-end --}}
            <div class="ff-btns">
                <button type="submit" class="btn btn-outline btn-sm">Search</button>
                @if($search || $divisionId || $status)
                    <a href="{{ route('employees.index') }}"
                       class="btn btn-sm" style="background:var(--bg);border:1.5px solid var(--border);color:var(--text-mid);">
                        Clear
                    </a>
                @endif
            </div>

        </form>
    </div>
</div>

{{-- ── Table ─────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3>Plantilla</h3>
        <span class="text-muted" style="font-size:0.82rem;">
            Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }}
            of {{ $employees->total() }}
        </span>
    </div>

    <div class="table-wrap">
        <table class="emp-table">
            <thead>
                <tr>
                    <th style="width:160px;">Plantilla No.</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th style="width:130px;">Division</th>
                    <th style="width:70px;text-align:center;">SG</th>
                    <th style="width:60px;text-align:center;">Step</th>
                    <th style="width:130px;text-align:right;">Basic Salary</th>
                    <th style="width:90px;text-align:center;">Status</th>
                    <th style="width:110px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $emp)

                {{-- Main visible row --}}
                <tr class="emp-main-row" data-id="{{ $emp->id }}" onclick="toggleEmpRow(this)">
                    <td class="col-plantilla">
                        <code style="font-size:0.76rem;color:var(--text-mid);">
                            {{ $emp->plantilla_item_no }}
                        </code>
                    </td>
                    <td class="col-name">
                        <span class="emp-expand-btn" aria-label="Expand">▼</span>
                        <a href="{{ route('employees.show', $emp) }}"
                           onclick="event.stopPropagation();"
                           style="font-weight:600;color:var(--navy);">
                            {{ $emp->full_name }}
                        </a>
                    </td>
                    <td class="col-position" style="font-size:0.85rem;">{{ $emp->position_title }}</td>
                    <td class="col-division">
                        @if ($emp->division)
                            <span class="badge" style="background:var(--navy-light);color:var(--navy);">
                                {{ $emp->division->code }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="col-sg" style="text-align:center;font-weight:600;">{{ $emp->salary_grade }}</td>
                    <td class="col-step" style="text-align:center;">{{ $emp->step }}</td>
                    <td class="col-salary" style="text-align:right;font-family:monospace;font-size:0.85rem;">
                        ₱{{ number_format($emp->basic_salary, 2) }}
                    </td>
                    <td class="col-status" style="text-align:center;">
                        @if ($emp->status === 'active')
                            <span class="badge badge-active">Active</span>
                        @elseif ($emp->status === 'inactive')
                            <span class="badge badge-inactive">Inactive</span>
                        @else
                            <span class="badge badge-draft">Vacant</span>
                        @endif
                    </td>
                    <td class="col-actions" style="text-align:center;">
                        <div class="d-flex gap-2" style="justify-content:center;">
                            <a href="{{ route('employees.show', $emp) }}"
                               class="btn btn-outline btn-sm" title="View">👁</a>
                            @role('payroll_officer|hrmo')
                            <a href="{{ route('employees.edit', $emp) }}"
                               class="btn btn-outline btn-sm" title="Edit">✎</a>
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                  onsubmit="return confirm('Remove {{ addslashes($emp->full_name) }} from the active plantilla?\n(Soft delete — record is preserved.)')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Remove">✕</button>
                            </form>
                            @endrole
                        </div>
                    </td>
                </tr>

                {{-- Expandable detail row (mobile only) --}}
                <tr class="emp-detail-row" id="detail-{{ $emp->id }}">
                    <td colspan="9">
                        <div class="emp-detail-grid">
                            <div class="emp-detail-item">
                                <label>Plantilla No.</label>
                                <span>
                                    <code style="font-size:0.78rem;color:var(--text-mid);">
                                        {{ $emp->plantilla_item_no }}
                                    </code>
                                </span>
                            </div>
                            <div class="emp-detail-item">
                                <label>Position</label>
                                <span>{{ $emp->position_title }}</span>
                            </div>
                            <div class="emp-detail-item">
                                <label>Salary Grade</label>
                                <span style="font-weight:600;">SG-{{ $emp->salary_grade }}, Step {{ $emp->step }}</span>
                            </div>
                            <div class="emp-detail-item">
                                <label>Basic Salary</label>
                                <span style="font-family:monospace;">₱{{ number_format($emp->basic_salary, 2) }}</span>
                            </div>
                        </div>
                        <div class="emp-detail-actions">
                            <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline btn-sm">👁 View</a>
                            @role('payroll_officer|hrmo')
                            <a href="{{ route('employees.edit', $emp) }}" class="btn btn-outline btn-sm">✎ Edit</a>
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}" style="flex:1;"
                                  onsubmit="return confirm('Remove {{ addslashes($emp->full_name) }} from the active plantilla?\n(Soft delete — record is preserved.)')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="width:100%;" title="Remove">✕ Remove</button>
                            </form>
                            @endrole
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--text-light);">
                        @if($search || $divisionId || $status)
                            No employees matched your filters.
                            <a href="{{ route('employees.index') }}">Clear filters →</a>
                        @else
                            No employees yet.
                            <a href="{{ route('employees.create') }}">Add the first employee →</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($employees->hasPages())
    <div style="padding:4px 20px 8px;">
        {{ $employees->links() }}
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function toggleEmpRow(mainRow) {
    const id      = mainRow.dataset.id;
    const detail  = document.getElementById('detail-' + id);
    const isOpen  = mainRow.classList.contains('open');

    // Only works on mobile — on desktop detail rows are always hidden
    if (window.innerWidth > 768) return;

    // Close all open rows first
    document.querySelectorAll('.emp-main-row.open').forEach(r => r.classList.remove('open'));
    document.querySelectorAll('.emp-detail-row.open').forEach(r => r.classList.remove('open'));

    // Toggle clicked row (unless it was already open)
    if (!isOpen) {
        mainRow.classList.add('open');
        detail.classList.add('open');
    }
}
</script>
@endsection
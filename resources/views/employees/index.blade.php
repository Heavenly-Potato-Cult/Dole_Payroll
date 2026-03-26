{{-- TODO: implement views/employees/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

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
              style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">

            <div style="flex:1;min-width:200px;">
                <label style="margin-bottom:4px;">Search</label>
                <input type="text" name="search"
                       value="{{ $search }}"
                       placeholder="Name, plantilla no., position…"
                       style="margin-bottom:0;">
            </div>

            <div style="min-width:180px;">
                <label style="margin-bottom:4px;">Division</label>
                <select name="division_id" style="margin-bottom:0;">
                    <option value="">All Divisions</option>
                    @foreach ($divisions as $div)
                        <option value="{{ $div->id }}"
                            {{ $divisionId == $div->id ? 'selected' : '' }}>
                            {{ $div->code }} — {{ $div->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width:130px;">
                <label style="margin-bottom:4px;">Status</label>
                <select name="status" style="margin-bottom:0;">
                    <option value="">All Statuses</option>
                    <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="vacant"   {{ $status === 'vacant'   ? 'selected' : '' }}>Vacant</option>
                </select>
            </div>

            <div style="display:flex;gap:8px;align-items:flex-end;">
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
        <table>
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
                <tr>
                    <td>
                        <code style="font-size:0.76rem;color:var(--text-mid);">
                            {{ $emp->plantilla_item_no }}
                        </code>
                    </td>
                    <td>
                        <a href="{{ route('employees.show', $emp) }}"
                           style="font-weight:600;color:var(--navy);">
                            {{ $emp->full_name }}
                        </a>
                    </td>
                    <td style="font-size:0.85rem;">{{ $emp->position_title }}</td>
                    <td>
                        @if ($emp->division)
                            <span class="badge" style="background:var(--navy-light);color:var(--navy);">
                                {{ $emp->division->code }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;font-weight:600;">{{ $emp->salary_grade }}</td>
                    <td style="text-align:center;">{{ $emp->step }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:0.85rem;">
                        ₱{{ number_format($emp->basic_salary, 2) }}
                    </td>
                    <td style="text-align:center;">
                        @if ($emp->status === 'active')
                            <span class="badge badge-active">Active</span>
                        @elseif ($emp->status === 'inactive')
                            <span class="badge badge-inactive">Inactive</span>
                        @else
                            <span class="badge badge-draft">Vacant</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
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

<?php \Log::info('VIEW END: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms'); ?>
@endsection
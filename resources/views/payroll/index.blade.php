{{-- resources/views/payroll/index.blade.php --}}
{{--
    CONTROLLER REQUIREMENT — PayrollController@index must eager-load aggregates:
    $query->withCount('entries')
          ->withSum('entries', 'gross_income')
          ->withSum('entries', 'total_deductions')
          ->withSum('entries', 'net_amount')

    Otherwise $batch->entries_count, ->entries_sum_gross_income, etc. will be null
    and the totals columns will show ₱0.00 for every row.
--}}

@extends('layouts.app')

@section('title', 'Regular Payroll')
@section('page-title', 'Regular Payroll')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Regular Payroll Batches</h1>
        <p>Semi-monthly payroll for all DOLE RO9 regular employees.</p>
    </div>
    @role('payroll_officer|hrmo')
    <a href="{{ route('payroll.create') }}" class="btn btn-primary">
        + New Payroll Batch
    </a>
    @endrole
</div>

{{-- ── Alerts ──────────────────────────────────────────────── --}}
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if (session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

{{-- ── Filter bar ──────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('payroll.index') }}"
              style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">

            <div class="form-group" style="margin:0; min-width:120px;">
                <label for="year" style="margin-bottom:4px;">Year</label>
                <select name="year" id="year">
                    <option value="">All Years</option>
                    @foreach (range(now()->year, 2020) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:140px;">
                <label for="month" style="margin-bottom:4px;">Month</label>
                <select name="month" id="month">
                    <option value="">All Months</option>
                    @foreach (['January','February','March','April','May','June',
                               'July','August','September','October','November','December']
                              as $i => $m)
                        <option value="{{ $i + 1 }}" {{ request('month') == $i + 1 ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:180px;">
                <label for="status" style="margin-bottom:4px;">Status</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="draft"               {{ request('status') === 'draft'               ? 'selected' : '' }}>Draft</option>
                    <option value="computed"            {{ request('status') === 'computed'            ? 'selected' : '' }}>Computed</option>
                    <option value="pending_accountant"  {{ request('status') === 'pending_accountant'  ? 'selected' : '' }}>Pending Accountant</option>
                    <option value="pending_rd"          {{ request('status') === 'pending_rd'          ? 'selected' : '' }}>Pending RD/ARD</option>
                    <option value="released"            {{ request('status') === 'released'            ? 'selected' : '' }}>Released</option>
                    <option value="locked"              {{ request('status') === 'locked'              ? 'selected' : '' }}>Locked</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>

        </form>
    </div>
</div>

{{-- ── Table ──────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Cut-off</th>
                        <th>Status</th>
                        <th class="text-right">Employees</th>
                        <th class="text-right">Total Gross</th>
                        <th class="text-right">Total Deductions</th>
                        <th class="text-right">Total Net Pay</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $months = [
                                '', 'January', 'February', 'March', 'April', 'May', 'June',
                                'July', 'August', 'September', 'October', 'November', 'December',
                            ];
                            $periodLabel = ($months[$batch->period_month] ?? '?')
                                . ' ' . ($batch->cutoff === '1st' ? '1–15' : '16–30/31')
                                . ', ' . $batch->period_year;

                            // Use withCount / withSum values pre-computed in the controller.
                            // Falls back gracefully to 0 if aggregates weren't loaded.
                            $entryCount = $batch->entries_count ?? 0;
                            $totalGross = $batch->entries_sum_gross_income ?? 0;
                            $totalDeds  = $batch->entries_sum_total_deductions ?? 0;
                            $totalNet   = $batch->entries_sum_net_amount ?? 0;

                            $statusClass = match ($batch->status) {
                                'draft'              => 'badge-draft',
                                'computed'           => 'badge-computed',
                                'pending_accountant',
                                'pending_rd'         => 'badge-pending',
                                'released'           => 'badge-released',
                                'locked'             => 'badge-locked',
                                default              => 'badge-draft',
                            };

                            // Human-readable label matching the controller's STATUS_LABELS
                            $statusLabels = [
                                'draft'               => 'Draft',
                                'computed'            => 'Computed',
                                'pending_accountant'  => 'Pending Accountant',
                                'pending_rd'          => 'Pending RD / ARD',
                                'released'            => 'Released',
                                'locked'              => 'Locked',
                            ];
                            $statusLabel = $statusLabels[$batch->status] ?? ucfirst(str_replace('_', ' ', $batch->status));
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $periodLabel }}</td>

                            <td>
                                <span class="badge {{ $batch->cutoff === '1st' ? 'badge-computed' : 'badge-released' }}">
                                    {{ $batch->cutoff }} Cut-off
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>

                            <td class="text-right">{{ $entryCount }}</td>

                            <td class="text-right">
                                {{ $entryCount > 0 ? '₱' . number_format($totalGross, 2) : '—' }}
                            </td>

                            <td class="text-right">
                                {{ $entryCount > 0 ? '₱' . number_format($totalDeds, 2) : '—' }}
                            </td>

                            <td class="text-right fw-bold">
                                {{ $entryCount > 0 ? '₱' . number_format($totalNet, 2) : '—' }}
                            </td>

                            <td class="text-muted" style="font-size:0.82rem;">
                                {{ $batch->creator->name ?? '—' }}<br>
                                <span style="font-size:0.75rem;">
                                    {{ $batch->created_at->format('M d, Y') }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('payroll.show', $batch) }}"
                                       class="btn btn-outline btn-sm">View</a>

                                    @role('payroll_officer|hrmo')
                                        @if ($batch->status === 'draft')
                                            <form method="POST"
                                                  action="{{ route('payroll.destroy', $batch) }}"
                                                  onsubmit="return confirm('Delete this draft batch? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    @endrole
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:40px; color:var(--text-light);">
                                No payroll batches found.
                                @role('payroll_officer|hrmo')
                                    <a href="{{ route('payroll.create') }}">Create one now →</a>
                                @endrole
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:12px;">{{ $batches->links() }}</div>

@endsection
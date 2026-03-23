@extends('layouts.app')

@section('title', 'Regular Payroll')
@section('page-title', 'Regular Payroll')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Regular Payroll Batches</h1>
        <p>Semi-monthly payroll for all 82 DOLE RO9 regular employees.</p>
    </div>
    @role('payroll_officer|hrmo')
    <a href="{{ route('payroll.create') }}" class="btn btn-primary">
        + New Payroll Batch
    </a>
    @endrole
</div>

{{-- ── Filter bar ── --}}
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
                    @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                        <option value="{{ $i + 1 }}" {{ request('month') == $i+1 ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:160px;">
                <label for="status" style="margin-bottom:4px;">Status</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="draft"              {{ request('status') === 'draft'              ? 'selected' : '' }}>Draft</option>
                    <option value="computed"           {{ request('status') === 'computed'           ? 'selected' : '' }}>Computed</option>
                    <option value="pending_accountant" {{ request('status') === 'pending_accountant' ? 'selected' : '' }}>Pending Accountant</option>
                    <option value="pending_rd"         {{ request('status') === 'pending_rd'         ? 'selected' : '' }}>Pending RD/ARD</option>
                    <option value="released"           {{ request('status') === 'released'           ? 'selected' : '' }}>Released</option>
                    <option value="locked"             {{ request('status') === 'locked'             ? 'selected' : '' }}>Locked</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>

        </form>
    </div>
</div>

{{-- ── Table ── --}}
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
                            $months = ['','January','February','March','April','May','June',
                                       'July','August','September','October','November','December'];
                            $periodLabel = $months[$batch->period_month] . ' ' .
                                           ($batch->cutoff === '1st' ? '1–15' : '16–30/31') .
                                           ', ' . $batch->period_year;

                            $entryCount  = $batch->entries_count ?? $batch->entries->count();
                            $totalGross  = $batch->entries->sum('gross_income');
                            $totalDeds   = $batch->entries->sum('total_deductions');
                            $totalNet    = $batch->entries->sum('net_amount');

                            $statusClass = match($batch->status) {
                                'draft'              => 'badge-draft',
                                'computed'           => 'badge-computed',
                                'pending_accountant',
                                'pending_rd'         => 'badge-pending',
                                'released'           => 'badge-released',
                                'locked'             => 'badge-locked',
                                default              => 'badge-draft',
                            };
                            $statusLabel = ucfirst(str_replace('_', ' ', $batch->status));
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $periodLabel }}</td>
                            <td>
                                <span class="badge {{ $batch->cutoff === '1st' ? 'badge-computed' : 'badge-released' }}">
                                    {{ $batch->cutoff }} Cut-off
                                </span>
                            </td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="text-right">{{ $entryCount }}</td>
                            <td class="text-right">₱{{ number_format($totalGross, 2) }}</td>
                            <td class="text-right">₱{{ number_format($totalDeds, 2) }}</td>
                            <td class="text-right fw-bold">₱{{ number_format($totalNet, 2) }}</td>
                            <td class="text-muted" style="font-size:0.82rem;">
                                {{ $batch->creator->name ?? '—' }}<br>
                                <span style="font-size:0.75rem;">{{ $batch->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('payroll.show', $batch) }}"
                                       class="btn btn-outline btn-sm">View</a>
                                    @role('payroll_officer|hrmo')
                                    @if ($batch->status === 'draft')
                                        <form method="POST" action="{{ route('payroll.destroy', $batch) }}"
                                              onsubmit="return confirm('Delete this draft batch?')">
                                            @csrf @method('DELETE')
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

<div>{{ $batches->links() }}</div>

@endsection

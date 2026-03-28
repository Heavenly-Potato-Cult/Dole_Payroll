{{-- resources/views/special-payroll/newly-hired-index.blade.php --}}
{{--
    Expects from SpecialPayrollController@newHireIndex:
      $batches     — paginated SpecialPayrollBatch (with employee), type='newly_hired'
      $currentYear — int
--}}

@extends('layouts.app')

@section('title', 'Pro-Rated Payroll — Newly Hired')
@section('page-title', 'Special Payroll')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Pro-Rated Payroll — Newly Hired / Transferee</h1>
        <p>Individual payroll records for employees who started mid-period.</p>
    </div>
    @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
        <a href="{{ route('special-payroll.newly-hired.create') }}" class="btn btn-primary">
            + New Entry
        </a>
    @endif
</div>

{{-- ── Alerts ── --}}
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if (session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

{{-- ── Filter bar ── --}}
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('special-payroll.newly-hired.index') }}"
              style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">

            <div class="form-group" style="margin:0; min-width:120px;">
                <label for="year" style="margin-bottom:4px;">Year</label>
                <select name="year" id="year">
                    <option value="">All Years</option>
                    @foreach (range($currentYear, $currentYear - 3) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:160px;">
                <label for="status" style="margin-bottom:4px;">Status</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('special-payroll.newly-hired.index') }}" class="btn btn-outline btn-sm">Reset</a>
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
                        <th>Employee</th>
                        <th>Position</th>
                        <th>Effectivity Date</th>
                        <th>Period Covered</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th class="text-right">Gross Earned</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Net Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $emp = $batch->employee;

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

                            $periodStart = $batch->period_start ? $batch->period_start->format('M d') : '—';
                            $periodEnd   = $batch->period_end   ? $batch->period_end->format('d, Y')   : '—';
                        @endphp
                        <tr>
                            <td class="fw-bold">
                                {{ optional($emp)->last_name }},
                                {{ optional($emp)->first_name }}
                                @if (optional($emp)->middle_name)
                                    {{ substr($emp->middle_name, 0, 1) }}.
                                @endif
                            </td>

                            <td class="text-muted" style="font-size:0.82rem;">
                                {{ optional($emp)->position_title ?? '—' }}
                            </td>

                            <td>
                                {{ $batch->effectivity_date ? $batch->effectivity_date->format('M d, Y') : '—' }}
                            </td>

                            <td class="text-muted" style="font-size:0.82rem;">
                                {{ $periodStart }}–{{ $periodEnd }}
                            </td>

                            <td>{{ $batch->year }}</td>

                            <td>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>

                            <td class="text-right">
                                ₱{{ number_format($batch->gross_amount, 2) }}
                            </td>

                            <td class="text-right" style="color:#B71C1C;">
                                ₱{{ number_format($batch->deductions_amount, 2) }}
                            </td>

                            <td class="text-right fw-bold" style="color:#1B5E20;">
                                ₱{{ number_format($batch->net_amount, 2) }}
                            </td>

<td>
    <div class="d-flex gap-2" style="justify-content:center;">
        <a href="{{ route('special-payroll.newly-hired.show', $batch->id) }}"
           class="btn btn-outline btn-sm">View</a>
        @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
            @if ($batch->status === 'draft')
                <form method="POST"
                      action="{{ route('special-payroll.newly-hired.destroy', $batch->id) }}"
                      onsubmit="return confirm('Delete this payroll record for {{ addslashes($batch->employee->last_name ?? '') }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">✕</button>
                </form>
            @endif
        @endif
    </div>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; padding:40px; color:var(--text-light);">
                                No records found.
                                @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
                                    <a href="{{ route('special-payroll.newly-hired.create') }}">
                                        Create one now →
                                    </a>
                                @endif
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
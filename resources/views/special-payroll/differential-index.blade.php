{{-- resources/views/special-payroll/differential-index.blade.php --}}
{{--
    Expects from SpecialPayrollController@differentialIndex:
      $batches     — paginated SpecialPayrollBatch (with employee), type='salary_differential'
      $currentYear — int
--}}

@extends('layouts.app')

@section('title', 'Salary Differential Records')
@section('page-title', 'Special Payroll')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Salary Differential</h1>
        <p>Payroll records for promotions, step increments, and salary adjustments.</p>
    </div>
    @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
        <a href="{{ route('special-payroll.differential.create') }}" class="btn btn-primary">
            + New Entry
        </a>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ── Filter bar ── --}}
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('special-payroll.differential.index') }}"
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
                <a href="{{ route('special-payroll.differential.index') }}" class="btn btn-outline btn-sm">Reset</a>
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
                        <th>Effectivity Period</th>
                        <th class="text-right">Old Rate</th>
                        <th class="text-right">New Rate</th>
                        <th class="text-right">Differential</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th class="text-right">Total Earned</th>
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

                            <td class="text-muted" style="font-size:0.82rem;">
                                @if ($batch->period_start && $batch->period_end)
                                    {{ $batch->period_start->format('M d, Y') }}
                                    –
                                    {{ $batch->period_end->format('M d, Y') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="text-right">
                                ₱{{ number_format($batch->old_basic_salary, 2) }}
                            </td>

                            <td class="text-right">
                                ₱{{ number_format($batch->new_basic_salary, 2) }}
                            </td>

                            <td class="text-right fw-bold" style="color:var(--navy);">
                                ₱{{ number_format($batch->differential_amount, 2) }}
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
        <a href="{{ route('special-payroll.differential.show', $batch->id) }}"
           class="btn btn-outline btn-sm">View</a>

        @if ($batch->status === 'draft' && auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
            <form method="POST"
                  action="{{ route('special-payroll.differential.destroy', $batch->id) }}"
                  onsubmit="return confirm('Delete this draft record? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm"
                        style="background:#B71C1C; color:#fff; border:none; cursor:pointer;">
                    ✕
                </button>
            </form>
        @endif
    </div>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align:center; padding:40px; color:var(--text-light);">
                                No records found.
                                @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
                                    <a href="{{ route('special-payroll.differential.create') }}">
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
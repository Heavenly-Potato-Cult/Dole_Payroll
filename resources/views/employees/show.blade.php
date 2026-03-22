@extends('layouts.app')

@section('title', $employee->full_name)
@section('page-title', 'Employee Profile')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $employee->full_name }}</h1>
        <p>{{ $employee->position_title }}
            @if ($employee->division)
                &mdash; <strong>{{ $employee->division->code }}</strong>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @role('payroll_officer|hrmo')
        <a href="{{ route('employees.deductions', $employee) }}" class="btn btn-outline">
            💳 Deductions
        </a>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
            ✎ Edit
        </a>
        @endrole
        <a href="{{ route('employees.index') }}" class="btn btn-outline">← Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    {{-- ── Left: Identity + Position ──────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card">
            <div class="card-header">
                <h3>Personal Information</h3>
                @if ($employee->status === 'active')
                    <span class="badge badge-active">Active</span>
                @elseif ($employee->status === 'inactive')
                    <span class="badge badge-inactive">Inactive</span>
                @else
                    <span class="badge badge-draft">Vacant</span>
                @endif
            </div>
            <div class="card-body">
                @include('employees._detail_row', ['label' => 'Full Name',    'value' => $employee->full_name])
                @include('employees._detail_row', ['label' => 'Last Name',    'value' => $employee->last_name])
                @include('employees._detail_row', ['label' => 'First Name',   'value' => $employee->first_name])
                @include('employees._detail_row', ['label' => 'Middle Name',  'value' => $employee->middle_name ?: '—'])
                @include('employees._detail_row', ['label' => 'Suffix',       'value' => $employee->suffix ?: '—'])
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Position & Assignment</h3></div>
            <div class="card-body">
                @include('employees._detail_row', ['label' => 'Plantilla Item No.', 'value' => $employee->plantilla_item_no, 'mono' => true])
                @include('employees._detail_row', ['label' => 'Position Title',     'value' => $employee->position_title])
                @include('employees._detail_row', ['label' => 'Division',
                    'value' => $employee->division
                        ? $employee->division->code . ' — ' . $employee->division->name
                        : '—'])
                @include('employees._detail_row', ['label' => 'Hire Date',
                    'value' => $employee->hire_date
                        ? $employee->hire_date->format('F d, Y')
                        : '—'])
            </div>
        </div>

        {{-- Promotion History --}}
        @if ($employee->promotionHistory->count())
        <div class="card">
            <div class="card-header">
                <h3>Promotion / Step History</h3>
                <span class="text-muted" style="font-size:0.82rem;">
                    {{ $employee->promotionHistory->count() }} {{ Str::plural('record', $employee->promotionHistory->count()) }}
                </span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Effective Date</th>
                            <th>SG</th>
                            <th>Step</th>
                            <th style="text-align:right;">Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employee->promotionHistory as $hist)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($hist->effective_date)->format('M d, Y') }}</td>
                            <td>{{ $hist->salary_grade }}</td>
                            <td>{{ $hist->step }}</td>
                            <td style="text-align:right;font-family:monospace;">
                                ₱{{ number_format($hist->basic_salary, 2) }}
                            </td>
                            <td style="font-size:0.82rem;color:var(--text-mid);">{{ $hist->remarks ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right: Salary + Gov IDs ─────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card">
            <div class="card-header"><h3>Salary Information</h3></div>
            <div class="card-body">
                @include('employees._detail_row', ['label' => 'Salary Grade',       'value' => 'SG ' . $employee->salary_grade])
                @include('employees._detail_row', ['label' => 'Step',               'value' => 'Step ' . $employee->step])
                @include('employees._detail_row', ['label' => 'SIT Year',           'value' => 'CY ' . $employee->sit_year])
                @include('employees._detail_row', ['label' => 'Basic Salary',       'value' => '₱' . number_format($employee->basic_salary, 2), 'bold' => true])
                @include('employees._detail_row', ['label' => 'PERA',               'value' => '₱' . number_format($employee->pera, 2)])

                <hr style="border:none;border-top:1px solid var(--border);margin:14px 0;">

                @include('employees._detail_row', ['label' => 'Daily Rate (÷22)',     'value' => '₱' . number_format($employee->daily_rate, 4), 'mono' => true])
                @include('employees._detail_row', ['label' => 'Hourly Rate (÷22÷8)',  'value' => '₱' . number_format($employee->hourly_rate, 4), 'mono' => true])
                @include('employees._detail_row', ['label' => 'Minute Rate',          'value' => '₱' . number_format($employee->minute_rate, 6), 'mono' => true])
                @include('employees._detail_row', ['label' => 'Semi-monthly Gross',   'value' => '₱' . number_format($employee->semi_monthly_gross, 2), 'bold' => true])
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Government IDs</h3></div>
            <div class="card-body">
                @include('employees._detail_row', ['label' => 'TIN',         'value' => $employee->tin         ?: '—', 'mono' => true])
                @include('employees._detail_row', ['label' => 'GSIS No.',    'value' => $employee->gsis_bp_no     ?: '—', 'mono' => true])
                @include('employees._detail_row', ['label' => 'Pag-IBIG',    'value' => $employee->pagibig_no  ?: '—', 'mono' => true])
                @include('employees._detail_row', ['label' => 'PhilHealth',  'value' => $employee->philhealth_no ?: '—', 'mono' => true])
                @include('employees._detail_row', ['label' => 'SSS No.',     'value' => $employee->sss_no      ?: '—', 'mono' => true])
            </div>
        </div>

        <div class="card" style="background:var(--bg);">
            <div class="card-body" style="font-size:0.78rem;color:var(--text-light);">
                <strong style="color:var(--text-mid);">Record created:</strong>
                {{ $employee->created_at->format('M d, Y g:i A') }}<br>
                <strong style="color:var(--text-mid);">Last updated:</strong>
                {{ $employee->updated_at->format('M d, Y g:i A') }}
            </div>
        </div>

    </div>

</div>

@endsection
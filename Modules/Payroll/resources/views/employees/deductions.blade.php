@extends('layouts.app')

@section('title', 'Deductions — ' . $employee->full_name)
@section('page-title', 'Employee Deductions')

@section('styles')
<style>
.deductions-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
.deductions-col    { display: flex; flex-direction: column; gap: 20px; }

@media (max-width: 800px) {
    .deductions-layout { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Deductions</h1>
        <p>
            {{ $employee->full_name }} &mdash;
            {{ $employee->position_title }}
            @if($employee->division)
                &mdash; <strong>{{ $employee->division->code }}</strong>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline">← Profile</a>
        <a href="{{ route('employees.index') }}" class="btn btn-outline">← All Employees</a>
    </div>
</div>

<div class="alert alert-info" style="margin-bottom:20px;">
    <div>
        <strong>How this works:</strong>
        Tick the checkbox to enroll an employee in a deduction and enter the monthly amount.
        <strong>Computed deductions</strong> (PhilHealth, GSIS Life/Ret, PAG-IBIG I, W/Holding Tax)
        are calculated automatically — you cannot set amounts for those here.
        All amounts are <strong>monthly totals</strong>; the payroll engine halves them per cut-off.
    </div>
</div>

<form method="POST" action="{{ route('employees.deductions.update', $employee) }}">
@csrf

<div class="deductions-layout">

    @php
        $grouped = $deductionTypes->groupBy('category');
        $categoryLabels = [
            'pagibig'    => 'PAG-IBIG / HDMF',
            'philhealth' => 'PhilHealth',
            'gsis'       => 'GSIS',
            'other_gov'  => 'Government / Tax',
            'loan'       => 'Bank Loans',
            'caress'     => 'CARESS IX',
            'misc'       => 'Miscellaneous',
        ];
        $leftCategories  = ['pagibig', 'philhealth', 'gsis'];
        $rightCategories = ['other_gov', 'loan', 'caress', 'misc'];
    @endphp

    {{-- Left column --}}
    <div class="deductions-col">
        @foreach ($leftCategories as $cat)
            @if (isset($grouped[$cat]))
                @include('payroll::employees._deduction_category', [
                    'label'       => $categoryLabels[$cat],
                    'types'       => $grouped[$cat],
                    'enrollments' => $enrollments,
                    'employee'    => $employee,
                ])
            @endif
        @endforeach
    </div>

    {{-- Right column --}}
    <div class="deductions-col">
        @foreach ($rightCategories as $cat)
            @if (isset($grouped[$cat]))
                @include('payroll::employees._deduction_category', [
                    'label'       => $categoryLabels[$cat],
                    'types'       => $grouped[$cat],
                    'enrollments' => $enrollments,
                    'employee'    => $employee,
                ])
            @endif
        @endforeach

        {{-- Summary card --}}
        <div class="card" id="deductionSummaryCard">
            <div class="card-header"><h3>Monthly Deduction Summary</h3></div>
            <div class="card-body">
                <div style="font-size:0.85rem;color:var(--text-mid);margin-bottom:12px;">
                    Manual deductions only. Computed deductions (PhilHealth, GSIS, PAG-IBIG I, WHT)
                    are excluded — they are calculated during payroll run.
                </div>
                <div style="display:flex;justify-content:space-between;
                            padding:10px 0;border-top:2px solid var(--navy);
                            font-weight:700;font-size:1.05rem;color:var(--navy);">
                    <span>Total Manual Deductions</span>
                    <span id="totalDeductionsDisplay">₱0.00</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;flex-direction:column;gap:10px;">
            <button type="submit" class="btn btn-primary btn-lg w-100">✓ Save Deductions</button>
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline w-100">Cancel</a>
        </div>
    </div>

</div>
</form>

@endsection

@section('scripts')
<script>
function recalcTotal() {
    let total = 0;
    document.querySelectorAll('.deduction-row').forEach(function (row) {
        const cb  = row.querySelector('.deduction-checkbox');
        const amt = row.querySelector('.deduction-amount');
        if (cb && cb.checked && amt) {
            const val = parseFloat(amt.value.replace(/,/g, '')) || 0;
            total += val;
        }
    });
    document.getElementById('totalDeductionsDisplay').textContent =
        '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.deduction-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const row    = this.closest('.deduction-row');
            const amtRow = row.querySelector('.deduction-amount-row');
            if (amtRow) amtRow.style.display = this.checked ? 'block' : 'none';
            recalcTotal();
        });
    });

    document.querySelectorAll('.deduction-amount').forEach(function (inp) {
        inp.addEventListener('input', recalcTotal);
    });

    recalcTotal();
});
</script>
@endsection
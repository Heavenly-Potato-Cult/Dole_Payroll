@extends('layouts.app')

@section('title', 'Add Promotion — ' . $employee->full_name)
@section('page-title', 'Add Promotion / Step Record')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Add Promotion / Step Record</h1>
        <p>{{ $employee->full_name }} &mdash; Currently SG {{ $employee->salary_grade }}, Step {{ $employee->step }}</p>
    </div>
    <a href="{{ route('employees.promotions.index', $employee) }}" class="btn btn-outline">← Back</a>
</div>

<div style="max-width:700px;">

    {{-- Current salary reference --}}
    <div class="alert alert-info" style="margin-bottom:20px;">
        <div>
            <strong>Current:</strong>
            SG {{ $employee->salary_grade }}, Step {{ $employee->step }} —
            <strong>₱{{ number_format($employee->basic_salary, 2) }}/month</strong>
            (CY {{ $employee->sit_year }}).
            New salary must be ≥ current salary.
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Promotion Details</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.promotions.store', $employee) }}">
                @csrf

                <div class="form-group">
                    <label for="type">Record Type <span style="color:var(--red)">*</span></label>
                    <select id="type" name="type" required>
                        @foreach (\App\Models\EmployeePromotionHistory::TYPES as $val => $label)
                            <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="effective_date">Effective Date <span style="color:var(--red)">*</span></label>
                    <input type="date" id="effective_date" name="effective_date"
                           value="{{ old('effective_date') }}"
                           class="{{ $errors->has('effective_date') ? 'is-invalid' : '' }}"
                           required style="max-width:220px;">
                    <div style="font-size:0.78rem;color:var(--text-light);margin-top:4px;">
                        Only one promotion record allowed per calendar month.
                    </div>
                    @error('effective_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid;grid-template-columns:130px 110px 150px 1fr;gap:14px;align-items:end;">

                    <div class="form-group" style="margin-bottom:0;">
                        <label for="new_sg">New SG <span style="color:var(--red)">*</span></label>
                        <select id="new_sg" name="new_sg" required>
                            <option value="">—</option>
                            @for ($sg = 1; $sg <= 33; $sg++)
                                <option value="{{ $sg }}"
                                    {{ old('new_sg', $employee->salary_grade) == $sg ? 'selected' : '' }}>
                                    SG {{ $sg }}
                                </option>
                            @endfor
                        </select>
                        @error('new_sg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label for="new_step">New Step <span style="color:var(--red)">*</span></label>
                        <select id="new_step" name="new_step" required>
                            <option value="">—</option>
                            @for ($s = 1; $s <= 8; $s++)
                                <option value="{{ $s }}"
                                    {{ old('new_step', $employee->step) == $s ? 'selected' : '' }}>
                                    Step {{ $s }}
                                </option>
                            @endfor
                        </select>
                        @error('new_step')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label for="sit_year">SIT Year <span style="color:var(--red)">*</span></label>
                        <select id="sit_year" name="sit_year" required>
                            @foreach ($sitYears as $yr)
                                <option value="{{ $yr }}"
                                    {{ old('sit_year', $employee->sit_year) == $yr ? 'selected' : '' }}>
                                    CY {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label for="new_salary">
                            New Basic Salary <span style="color:var(--red)">*</span>
                            <span id="sit_status" style="font-weight:400;font-size:0.76rem;
                                                          color:var(--success);margin-left:4px;"></span>
                        </label>
                        <input type="hidden" id="basic_salary_raw" name="new_salary"
                               value="{{ old('new_salary') }}">
                        <input type="text" id="basic_salary"
                               value="{{ old('new_salary') ? number_format(old('new_salary'),2) : '' }}"
                               placeholder="Auto-filled from SIT"
                               class="{{ $errors->has('new_salary') ? 'is-invalid' : '' }}"
                               readonly
                               style="background:var(--bg);font-family:monospace;">
                        @error('new_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>

                {{-- Differential preview --}}
                <div id="differentialPreview"
                     style="display:none;margin-top:14px;padding:10px 16px;
                            background:var(--success-bg);border-radius:6px;
                            font-size:0.85rem;color:var(--success);font-weight:600;">
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="2"
                              maxlength="500"
                              placeholder="e.g. Per CSC appointment paper dated …">{{ old('remarks') }}</textarea>
                </div>

                <div style="display:flex;gap:10px;padding-top:8px;
                            border-top:1px solid var(--border);margin-top:8px;">
                    <button type="submit" class="btn btn-primary">✓ Save & Update Employee</button>
                    <a href="{{ route('employees.promotions.index', $employee) }}" class="btn btn-outline">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/sit-lookup.js') }}"></script>
<script>
const currentSalary = {{ $employee->basic_salary }};

SITLookup.init({
    sgId     : 'new_sg',
    stepId   : 'new_step',
    yearId   : 'sit_year',
    salaryId : 'basic_salary',
    statusId : 'sit_status',
    apiUrl   : '{{ route("api.sit") }}',
});

document.addEventListener('DOMContentLoaded', function () {
    const display  = document.getElementById('basic_salary');
    const raw      = document.getElementById('basic_salary_raw');
    const preview  = document.getElementById('differentialPreview');

    const observer = new MutationObserver(function () {
        if (display.dataset.rawAmount) {
            raw.value = display.dataset.rawAmount;
            const diff = parseFloat(display.dataset.rawAmount) - currentSalary;
            if (!isNaN(diff)) {
                preview.style.display = 'block';
                preview.textContent = 'Salary differential: ₱' +
                    diff.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) +
                    ' / month';
                preview.style.background = diff >= 0 ? 'var(--success-bg)' : 'var(--red-light)';
                preview.style.color = diff >= 0 ? 'var(--success)' : 'var(--red)';
            }
        }
    });
    observer.observe(display, { attributes: true, attributeFilter: ['data-raw-amount'] });
});
</script>
@endsection
@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Employee</h1>
        <p>{{ $employee->full_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline">← View Profile</a>
        <a href="{{ route('employees.index') }}" class="btn btn-outline">← All Employees</a>
    </div>
</div>

<form method="POST" action="{{ route('employees.update', $employee) }}" id="employeeForm">
@csrf
@method('PUT')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- ── Left column ──────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Personal Information --}}
        <div class="card">
            <div class="card-header"><h3>Personal Information</h3></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:14px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="last_name">Last Name <span style="color:var(--red)">*</span></label>
                        <input type="text" id="last_name" name="last_name"
                               value="{{ old('last_name', $employee->last_name) }}"
                               class="{{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                               required maxlength="100" style="text-transform:uppercase;">
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="first_name">First Name <span style="color:var(--red)">*</span></label>
                        <input type="text" id="first_name" name="first_name"
                               value="{{ old('first_name', $employee->first_name) }}"
                               class="{{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                               required maxlength="100">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name"
                               value="{{ old('middle_name', $employee->middle_name) }}" maxlength="100">
                    </div>
                    <div class="form-group" style="margin-bottom:0;min-width:80px;">
                        <label for="suffix">Suffix</label>
                        <input type="text" id="suffix" name="suffix"
                               value="{{ old('suffix', $employee->suffix) }}" maxlength="20">
                    </div>
                </div>
            </div>
        </div>

        {{-- Position & Assignment --}}
        <div class="card">
            <div class="card-header"><h3>Position & Assignment</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="plantilla_item_no">Plantilla Item No. <span style="color:var(--red)">*</span></label>
                    <input type="text" id="plantilla_item_no" name="plantilla_item_no"
                           value="{{ old('plantilla_item_no', $employee->plantilla_item_no) }}"
                           class="{{ $errors->has('plantilla_item_no') ? 'is-invalid' : '' }}"
                           required maxlength="100">
                    @error('plantilla_item_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="position_title">Position Title <span style="color:var(--red)">*</span></label>
                        <input type="text" id="position_title" name="position_title"
                               value="{{ old('position_title', $employee->position_title) }}"
                               class="{{ $errors->has('position_title') ? 'is-invalid' : '' }}"
                               required maxlength="200">
                        @error('position_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="division_id">Division <span style="color:var(--red)">*</span></label>
                        <select id="division_id" name="division_id"
                                class="{{ $errors->has('division_id') ? 'is-invalid' : '' }}" required>
                            <option value="">— Select —</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div->id }}"
                                    {{ old('division_id', $employee->division_id) == $div->id ? 'selected' : '' }}>
                                    {{ $div->code }} — {{ $div->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Salary --}}
        <div class="card">
            <div class="card-header">
                <h3>Salary</h3>
                <span class="text-muted" style="font-size:0.80rem;">Change SG/Step to re-lookup from SIT</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:120px 100px 140px 1fr;gap:14px;align-items:end;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="salary_grade">Salary Grade <span style="color:var(--red)">*</span></label>
                        <select id="salary_grade" name="salary_grade" required>
                            <option value="">—</option>
                            @for ($sg = 1; $sg <= 33; $sg++)
                                <option value="{{ $sg }}"
                                    {{ old('salary_grade', $employee->salary_grade) == $sg ? 'selected' : '' }}>
                                    SG {{ $sg }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="step">Step <span style="color:var(--red)">*</span></label>
                        <select id="step" name="step" required>
                            <option value="">—</option>
                            @for ($s = 1; $s <= 8; $s++)
                                <option value="{{ $s }}"
                                    {{ old('step', $employee->step) == $s ? 'selected' : '' }}>
                                    Step {{ $s }}
                                </option>
                            @endfor
                        </select>
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
                        <label for="basic_salary">
                            Basic Salary <span style="color:var(--red)">*</span>
                            <span id="sit_status" style="font-weight:400;font-size:0.76rem;color:var(--success);margin-left:6px;"></span>
                        </label>
                        <input type="hidden" id="basic_salary_raw" name="basic_salary"
                               value="{{ old('basic_salary', $employee->basic_salary) }}">
                        <input type="text" id="basic_salary"
                               value="{{ old('basic_salary', number_format($employee->basic_salary, 2)) }}"
                               data-raw-amount="{{ old('basic_salary', $employee->basic_salary) }}"
                               class="{{ $errors->has('basic_salary') ? 'is-invalid' : '' }}"
                               readonly
                               style="background:var(--bg);font-family:monospace;">
                        @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-top:14px;">
                    <label for="pera">PERA</label>
                    <input type="number" id="pera" name="pera"
                           value="{{ old('pera', $employee->pera) }}"
                           min="0" step="0.01" style="max-width:180px;">
                </div>

                {{-- Rate breakdown (read-only info) --}}
                <div style="margin-top:14px;background:var(--bg);border-radius:6px;
                             padding:12px 16px;font-size:0.80rem;color:var(--text-mid);
                             display:flex;gap:24px;flex-wrap:wrap;">
                    <span><strong>Daily:</strong>
                        ₱{{ number_format($employee->daily_rate, 4) }}
                    </span>
                    <span><strong>Hourly:</strong>
                        ₱{{ number_format($employee->hourly_rate, 4) }}
                    </span>
                    <span><strong>Semi-monthly gross:</strong>
                        ₱{{ number_format($employee->semi_monthly_gross, 2) }}
                    </span>
                </div>

            </div>
        </div>

    </div>{{-- end left --}}

    {{-- ── Right column ─────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card">
            <div class="card-header"><h3>Employment</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="hire_date">Hire / Appointment Date</label>
                    <input type="date" id="hire_date" name="hire_date"
                           value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="status">Status <span style="color:var(--red)">*</span></label>
                    <select id="status" name="status" required>
                        <option value="active"   {{ old('status', $employee->status) === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="vacant"   {{ old('status', $employee->status) === 'vacant'   ? 'selected' : '' }}>Vacant</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Government IDs</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="tin">TIN</label>
                    <input type="text" id="tin" name="tin"
                           value="{{ old('tin', $employee->tin) }}" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="gsis_no">GSIS Number</label>
                    <input type="text" id="gsis_no" name="gsis_no"
                           value="{{ old('gsis_no', $employee->gsis_no) }}" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="pagibig_no">Pag-IBIG Number</label>
                    <input type="text" id="pagibig_no" name="pagibig_no"
                           value="{{ old('pagibig_no', $employee->pagibig_no) }}" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="philhealth_no">PhilHealth Number</label>
                    <input type="text" id="philhealth_no" name="philhealth_no"
                           value="{{ old('philhealth_no', $employee->philhealth_no) }}" maxlength="50">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="sss_no">SSS Number</label>
                    <input type="text" id="sss_no" name="sss_no"
                           value="{{ old('sss_no', $employee->sss_no) }}" maxlength="50">
                </div>
            </div>
        </div>

        <div class="card" style="background:var(--bg);">
            <div class="card-body" style="font-size:0.78rem;color:var(--text-light);">
                <strong style="color:var(--text-mid);">Created:</strong>
                {{ $employee->created_at->format('M d, Y g:i A') }}<br>
                <strong style="color:var(--text-mid);">Last updated:</strong>
                {{ $employee->updated_at->format('M d, Y g:i A') }}
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <button type="submit" class="btn btn-primary btn-lg w-100">✓ Save Changes</button>
            <a href="{{ route('employees.index') }}" class="btn btn-outline w-100">Cancel</a>
        </div>

    </div>

</div>

</form>

@endsection

@section('scripts')
<script src="{{ asset('js/sit-lookup.js') }}"></script>
<script>
SITLookup.init({
    sgId     : 'salary_grade',
    stepId   : 'step',
    yearId   : 'sit_year',
    salaryId : 'basic_salary',
    statusId : 'sit_status',
    apiUrl   : '{{ route("api.sit") }}',
});

document.addEventListener('DOMContentLoaded', function () {
    const display = document.getElementById('basic_salary');
    const raw     = document.getElementById('basic_salary_raw');

    const observer = new MutationObserver(function () {
        if (display.dataset.rawAmount) raw.value = display.dataset.rawAmount;
    });
    observer.observe(display, { attributes: true, attributeFilter: ['data-raw-amount'] });

    display.addEventListener('change', function () {
        raw.value = this.dataset.rawAmount || this.value.replace(/,/g, '');
    });

    ['last_name', 'first_name', 'middle_name'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', function () {
            const p = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(p, p);
        });
    });
});
</script>
@endsection
@extends('layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Add User</h1>
        <p>Create a new system account and assign a role</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline">← Back</a>
</div>

<div style="max-width:520px;">
    <div class="card">
        <div class="card-header"><h3>Account Details</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="form-group">
                    <label for="employee_id">Employee <span style="color:var(--red)">*</span></label>
                    <select id="employee_id" name="employee_id"
                            class="{{ $errors->has('employee_id') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select an employee —</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                    {{ old('employee_id') === $employee->id ? 'selected' : '' }}>
                                {{ $employee->last_name }}, {{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . '.' : '' }} ({{ $employee->employee_no }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- ── Primary role ──────────────────────────────────────────── --}}
                <div class="form-group">
                    <label for="role">Primary Role <span style="color:var(--red)">*</span></label>
                    <select id="role" name="role"
                            class="{{ $errors->has('role') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select a role —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                    {{ old('role') === $role->name ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px;">
                        The user's main role. Controls what modules they can access.
                    </div>
                </div>

                {{-- ── Secondary (alternate) role ───────────────────────────── --}}
                <div class="form-group">
                    <label for="secondary_role">
                        Secondary Role
                        <span style="font-weight:400; color:var(--text-light); font-size:0.82rem;">(optional)</span>
                    </label>
                    <select id="secondary_role" name="secondary_role"
                            class="{{ $errors->has('secondary_role') ? 'is-invalid' : '' }}">
                        <option value="">— None —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                    {{ old('secondary_role') === $role->name ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('secondary_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px;">
                        Assign if this person may act in another capacity (e.g. Payroll Officer covering HRMO).
                        The secondary role is <strong>inactive by default</strong> — activate it from the user list
                        when they are actually covering that role.
                    </div>
                </div>

                
                <div style="display:flex;gap:10px;margin-top:24px;">
                    <button type="submit" class="btn btn-primary">✓ Create User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

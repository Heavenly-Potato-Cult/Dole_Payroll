@extends('layouts.app')

@section('title', 'Edit Signatory')
@section('page-title', 'Edit Signatory')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Signatory</h1>
        <p>{{ $signatory->full_name }}</p>
    </div>
    <a href="{{ route('signatories.index') }}" class="btn btn-outline">← Back</a>
</div>

<div style="max-width:520px;">
    <div class="card">
        <div class="card-header"><h3>Officer Details</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('signatories.update', $signatory) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="role_type">Role <span style="color:var(--red)">*</span></label>
                    <select id="role_type" name="role_type"
                            class="{{ $errors->has('role_type') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select a role —</option>
                        <option value="hrmo_designate"
                            {{ old('role_type', $signatory->role_type) === 'hrmo_designate' ? 'selected' : '' }}>
                            HRMO Designate
                        </option>
                        <option value="accountant"
                            {{ old('role_type', $signatory->role_type) === 'accountant' ? 'selected' : '' }}>
                            Accountant
                        </option>
                        <option value="ard"
                            {{ old('role_type', $signatory->role_type) === 'ard' ? 'selected' : '' }}>
                            ARD / RD
                        </option>
                        <option value="cashier"
                            {{ old('role_type', $signatory->role_type) === 'cashier' ? 'selected' : '' }}>
                            Cashier
                        </option>
                    </select>
                    @error('role_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                           class="{{ $errors->has('full_name') ? 'is-invalid' : '' }}"
                           value="{{ old('full_name', $signatory->full_name) }}" required>
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="position_title">Position Title</label>
                    <input type="text" id="position_title" name="position_title"
                           class="{{ $errors->has('position_title') ? 'is-invalid' : '' }}"
                           value="{{ old('position_title', $signatory->position_title) }}"
                           placeholder="e.g. Labor Employment Officer III">
                    @error('position_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $signatory->is_active) ? 'checked' : '' }}
                               style="width:auto; accent-color:var(--navy);">
                        Active signatory
                    </label>
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px; padding-left:26px;">
                        Only one person per role can be active. Activating this will deactivate others.
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:24px;">
                    <button type="submit" class="btn btn-primary">✓ Save Changes</button>
                    <a href="{{ route('signatories.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

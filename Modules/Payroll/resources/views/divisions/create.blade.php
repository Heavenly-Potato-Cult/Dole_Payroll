@extends('layouts.app')

@section('title', 'New Division')
@section('page-title', 'New Division')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Division</h1>
        <p>Add an organisational division to DOLE RO9</p>
    </div>
    <a href="{{ route('divisions.index') }}" class="btn btn-outline">
        ← Back to Divisions
    </a>
</div>

<div style="max-width:640px;">
    <div class="card">
        <div class="card-header">
            <h3>Division Details</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('divisions.store') }}">
                @csrf

                {{-- Division Name --}}
                <div class="form-group">
                    <label for="name">Division Name <span style="color:var(--red)">*</span></label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="e.g. Internal Management Services Division"
                           class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                           required maxlength="200">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Division Code --}}
                <div class="form-group">
                    <label for="code">Division Code <span style="color:var(--red)">*</span></label>
                    <input type="text" id="code" name="code"
                           value="{{ old('code') }}"
                           placeholder="e.g. IMSD"
                           class="{{ $errors->has('code') ? 'is-invalid' : '' }}"
                           required maxlength="20"
                           style="text-transform:uppercase;max-width:200px;">
                    <div style="font-size:0.78rem;color:var(--text-light);margin-top:4px;">
                        Short abbreviation used in reports and payslips.
                    </div>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                              rows="3" maxlength="500"
                              placeholder="Brief description of this division's mandate…">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label>Status</label>
                    <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
                        <label style="display:flex;align-items:center;gap:8px;
                                      text-transform:none;letter-spacing:0;
                                      font-size:0.92rem;font-weight:400;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', '1') ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:var(--navy);">
                            Active (employees can be assigned to this division)
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:10px;padding-top:8px;border-top:1px solid var(--border);margin-top:8px;">
                    <button type="submit" class="btn btn-primary">
                        ✓ Create Division
                    </button>
                    <a href="{{ route('divisions.index') }}" class="btn btn-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Auto-uppercase the code field as user types
document.getElementById('code').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
@endsection
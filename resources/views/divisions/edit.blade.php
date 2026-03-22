@extends('layouts.app')

@section('title', 'Edit Division')
@section('page-title', 'Edit Division')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Division</h1>
        <p>Update details for <strong>{{ $division->name }}</strong></p>
    </div>
    <a href="{{ route('divisions.index') }}" class="btn btn-outline">
        ← Back to Divisions
    </a>
</div>

<div style="max-width:640px;">
    <div class="card">
        <div class="card-header">
            <h3>Division Details</h3>
            <code style="background:var(--navy-light);color:var(--navy);
                         padding:3px 10px;border-radius:4px;font-size:0.78rem;
                         font-weight:700;letter-spacing:0.04em;">
                {{ $division->code }}
            </code>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('divisions.update', $division) }}">
                @csrf
                @method('PUT')

                {{-- Division Name --}}
                <div class="form-group">
                    <label for="name">Division Name <span style="color:var(--red)">*</span></label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $division->name) }}"
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
                           value="{{ old('code', $division->code) }}"
                           placeholder="e.g. IMSD"
                           class="{{ $errors->has('code') ? 'is-invalid' : '' }}"
                           required maxlength="20"
                           style="text-transform:uppercase;max-width:200px;">
                    <div style="font-size:0.78rem;color:var(--text-light);margin-top:4px;">
                        Changing this code updates all associated reports.
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
                              placeholder="Brief description of this division's mandate…">{{ old('description', $division->description) }}</textarea>
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
                                   {{ old('is_active', $division->is_active) ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:var(--navy);">
                            Active
                        </label>
                    </div>
                    @if (!$division->is_active)
                        <div class="alert alert-warning mt-1" style="margin-top:10px;">
                            ⚠ This division is currently inactive. Employees can still be
                            assigned but it will be flagged on reports.
                        </div>
                    @endif
                </div>

                {{-- Meta info (read-only) --}}
                <div style="background:var(--bg);border-radius:6px;padding:12px 16px;
                             margin-bottom:18px;font-size:0.80rem;color:var(--text-light);
                             display:flex;gap:24px;flex-wrap:wrap;">
                    <span>
                        <strong style="color:var(--text-mid);">Employees assigned:</strong>
                        {{ $division->loadCount('employees')->employees_count }}
                    </span>
                    <span>
                        <strong style="color:var(--text-mid);">Created:</strong>
                        {{ $division->created_at->format('M d, Y') }}
                    </span>
                    <span>
                        <strong style="color:var(--text-mid);">Last updated:</strong>
                        {{ $division->updated_at->format('M d, Y g:i A') }}
                    </span>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:10px;padding-top:8px;border-top:1px solid var(--border);margin-top:4px;">
                    <button type="submit" class="btn btn-primary">
                        ✓ Save Changes
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
document.getElementById('code').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
@endsection
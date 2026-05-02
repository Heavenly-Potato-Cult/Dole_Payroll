@extends('layouts.app')

@section('title', 'Edit — ' . $deductionType->name)
@section('page-title', 'Deduction Types')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Deduction Type</h1>
        <p>
            <span style="font-family:monospace;background:var(--bg);border:1px solid var(--border);padding:1px 8px;border-radius:4px;font-size:0.85rem;">{{ $deductionType->code }}</span>
            @if ($deductionType->is_computed)
                &nbsp;<span style="background:#eef2ff;color:#4338ca;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:99px;border:1px solid #c7d2fe;">🔒 Auto-computed</span>
            @endif
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('deduction-types.index') }}" class="btn btn-outline">← Back to List</a>
    </div>
</div>

<div style="max-width:640px;">

    <div class="card">
        <div class="card-header"><h3>Deduction Type Details</h3></div>
        <div class="card-body">

            <form method="POST" action="{{ route('deduction-types.update', $deductionType) }}">
            @csrf
            @method('PUT')

                {{-- Code (read-only) --}}
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Code <span style="font-weight:400;color:var(--text-light);">(permanent — cannot be changed)</span>
                    </label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-family:monospace;background:var(--bg);border:1px solid var(--border);
                                     padding:8px 14px;border-radius:6px;font-size:0.9rem;
                                     color:var(--navy);letter-spacing:.04em;">
                            {{ $deductionType->code }}
                        </span>
                        <span style="font-size:0.72rem;color:var(--text-light);">🔒 Locked</span>
                    </div>
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        This code is used internally by the payroll engine and enrollment system.
                        Changing it would break existing payroll records.
                    </div>
                </div>

                {{-- Name --}}
                <div style="margin-bottom:18px;">
                    <label for="name" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Display Name <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $deductionType->name) }}"
                           placeholder="e.g. HDMF Multi-Purpose Loan"
                           maxlength="200"
                           required>
                    @error('name')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        This is the name shown on payslips, reports, and enrollment forms.
                    </div>
                </div>

                {{-- Category --}}
                <div style="margin-bottom:18px;">
                    <label for="category" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Category <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="category" name="category" required>
                        @foreach ($categoryLabels as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('category', $deductionType->category) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Display Order --}}
                <div style="margin-bottom:18px;">
                    <label for="display_order" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Display Order <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="number"
                           id="display_order"
                           name="display_order"
                           value="{{ old('display_order', $deductionType->display_order) }}"
                           min="0"
                           max="999"
                           required
                           style="max-width:120px;">
                    @error('display_order')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        Controls the order this deduction appears on payslips and reports.
                    </div>
                </div>

                {{-- Notes --}}
                <div style="margin-bottom:18px;">
                    <label for="notes" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Notes / Description <span style="color:var(--text-light);font-weight:400;">(optional)</span>
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              maxlength="500"
                              placeholder="e.g. HDMF Multi-Purpose Loan. Fixed monthly amortization per employee."
                              style="resize:vertical;">{{ old('notes', $deductionType->notes) }}</textarea>
                    @error('notes')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Computed type notice --}}
                @if ($deductionType->is_computed)
                <div class="alert alert-info" style="margin-bottom:20px;font-size:0.82rem;">
                    <strong>🔒 Auto-computed type.</strong>
                    The amount for this deduction is calculated automatically by the payroll engine
                    (based on BIR / GSIS / PhilHealth / HDMF rules). You can rename it or move it
                    to a different category, but you cannot change it to a manual enrollment type here.
                </div>
                @endif

                {{-- Current status info --}}
                <div style="padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:20px;font-size:0.82rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <strong>Current status:</strong>
                        @if ($deductionType->is_active)
                            <span style="color:#166534;font-weight:700;">● Active</span>
                            — appears in enrollment forms and payroll computation.
                        @else
                            <span style="color:#991b1b;font-weight:700;">● Inactive</span>
                            — hidden from enrollment forms and skipped during payroll.
                        @endif
                    </div>
                    <form method="POST"
                          action="{{ route('deduction-types.toggle', $deductionType) }}"
                          style="display:inline;"
                          onsubmit="return confirm('{{ $deductionType->is_active ? 'Deactivate' : 'Activate' }} this deduction type?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="btn {{ $deductionType->is_active ? 'btn-outline' : 'btn-primary' }}"
                                style="font-size:0.8rem;padding:6px 14px;">
                            {{ $deductionType->is_active ? '⊘ Deactivate' : '✓ Activate' }}
                        </button>
                    </form>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('deduction-types.index') }}" class="btn btn-outline">Cancel</a>
                </div>

            </form>

        </div>
    </div>

    {{-- Meta info --}}
    <div class="card" style="background:var(--bg);margin-top:16px;">
        <div class="card-body" style="font-size:0.78rem;color:var(--text-light);">
            <strong style="color:var(--text-mid);">Record created:</strong>
            {{ $deductionType->created_at->format('M d, Y g:i A') }}<br>
            <strong style="color:var(--text-mid);">Last updated:</strong>
            {{ $deductionType->updated_at->format('M d, Y g:i A') }}
        </div>
    </div>

</div>

@endsection

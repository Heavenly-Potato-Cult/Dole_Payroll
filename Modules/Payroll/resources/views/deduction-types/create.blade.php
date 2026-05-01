@extends('layouts.app')

@section('title', 'New Deduction Type')
@section('page-title', 'Deduction Types')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Deduction Type</h1>
        <p>Add a new deduction or loan type to the payroll system.</p>
    </div>
    <div>
        <a href="{{ route('deduction-types.index') }}" class="btn btn-outline">← Back to List</a>
    </div>
</div>

<div style="max-width:640px;">

    <div class="card">
        <div class="card-header"><h3>Deduction Type Details</h3></div>
        <div class="card-body">

            <form method="POST" action="{{ route('deduction-types.store') }}">
            @csrf

                {{-- Code --}}
                <div style="margin-bottom:18px;">
                    <label for="code" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Code <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text"
                           id="code"
                           name="code"
                           value="{{ old('code') }}"
                           placeholder="e.g. GSIS_NEW_LOAN"
                           maxlength="50"
                           required
                           autocomplete="off"
                           style="font-family:monospace;text-transform:uppercase;letter-spacing:.05em;"
                           oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')">
                    @error('code')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        ⚠ The code is <strong>permanent</strong> — it cannot be changed after saving.
                        Use uppercase letters, numbers, and underscores only (e.g. <code>HDMF_NEW_LOAN</code>).
                        This code links this deduction to the payroll engine.
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
                           value="{{ old('name') }}"
                           placeholder="e.g. HDMF New Loan"
                           maxlength="200"
                           required>
                    @error('name')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        This is the name shown on the payslip, enrollment forms, and reports.
                        It can be changed at any time.
                    </div>
                </div>

                {{-- Category --}}
                <div style="margin-bottom:18px;">
                    <label for="category" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Category <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="category" name="category" required>
                        <option value="">— Select category —</option>
                        @foreach ($categoryLabels as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        Groups this deduction on the payslip and enrollment form.
                    </div>
                </div>

                {{-- Display Order --}}
                <div style="margin-bottom:18px;">
                    <label for="display_order" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Display Order <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="number"
                           id="display_order"
                           name="display_order"
                           value="{{ old('display_order', $nextOrder) }}"
                           min="0"
                           max="999"
                           required
                           style="max-width:120px;">
                    @error('display_order')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:4px;">
                        Lower numbers appear first on payslips. Current highest is {{ $nextOrder - 1 }}.
                    </div>
                </div>

                {{-- Notes --}}
                <div style="margin-bottom:24px;">
                    <label for="notes" style="display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-mid);margin-bottom:5px;">
                        Notes / Description <span style="color:var(--text-light);font-weight:400;">(optional)</span>
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              maxlength="500"
                              placeholder="e.g. HDMF new loan program, fixed monthly amortization per employee."
                              style="resize:vertical;">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Info box --}}
                <div class="alert alert-info" style="margin-bottom:20px;font-size:0.82rem;">
                    <strong>Note:</strong> New deduction types created here are always <strong>manual</strong>
                    (amount set per employee). Auto-computed types (PAG-IBIG I, PhilHealth, GSIS Life/Ret,
                    Withholding Tax) are engine-owned and can only be modified via the seeder.
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">Save Deduction Type</button>
                    <a href="{{ route('deduction-types.index') }}" class="btn btn-outline">Cancel</a>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection

@extends('layouts.app')

@section('title', 'Create Payroll Batch')
@section('page-title', 'Create Payroll Batch')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Payroll Batch</h1>
        <p>Select the period and cut-off, then submit to generate all employee entries.</p>
    </div>
    <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-sm">← Back to Payroll List</a>
</div>

{{-- How it works info banner --}}
<div class="alert alert-info mb-3">
    <div>
        <strong>How batch creation works:</strong>
        Creating a batch immediately triggers payroll computation for all
        <strong>active employees</strong> using the 22-day fixed denominator.
        Deductions are pulled from each employee's active enrollment records.
        You can re-compute at any time before the batch is submitted for approval.
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

    {{-- ── Main Form ── --}}
    <div class="card">
        <div class="card-header">
            <h3>📅 Payroll Period</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('payroll.store') }}" id="createForm">
                @csrf

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">

                    {{-- Year --}}
                    <div class="form-group">
                        <label for="period_year">Year</label>
                        <select name="period_year" id="period_year"
                                class="{{ $errors->has('period_year') ? 'is-invalid' : '' }}"
                                onchange="updatePreview()">
                            @foreach ($years as $y)
                                <option value="{{ $y }}"
                                    {{ (old('period_year', $currentYear) == $y) ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                        @error('period_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Month --}}
                    <div class="form-group">
                        <label for="period_month">Month</label>
                        <select name="period_month" id="period_month"
                                class="{{ $errors->has('period_month') ? 'is-invalid' : '' }}"
                                onchange="updatePreview()">
                            @php
                                $months = [
                                    1=>'January', 2=>'February', 3=>'March',
                                    4=>'April',   5=>'May',      6=>'June',
                                    7=>'July',    8=>'August',   9=>'September',
                                    10=>'October',11=>'November',12=>'December',
                                ];
                            @endphp
                            @foreach ($months as $num => $label)
                                <option value="{{ $num }}"
                                    {{ (old('period_month', $currentMonth) == $num) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('period_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- Cut-off --}}
                <div class="form-group">
                    <label>Cut-off Period</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:4px;">

                        <label class="cutoff-card {{ old('cutoff', '1st') === '1st' ? 'cutoff-card--active' : '' }}"
                               for="cutoff_1st">
                            <input type="radio" name="cutoff" id="cutoff_1st" value="1st"
                                   {{ old('cutoff', '1st') === '1st' ? 'checked' : '' }}
                                   onchange="updatePreview()">
                            <div class="cutoff-card-body">
                                <strong>1st Cut-off</strong>
                                <span>Coverage: 1–15</span>
                                <span class="text-muted" style="font-size:0.78rem;">Released on the 10th</span>
                            </div>
                        </label>

                        <label class="cutoff-card {{ old('cutoff') === '2nd' ? 'cutoff-card--active' : '' }}"
                               for="cutoff_2nd">
                            <input type="radio" name="cutoff" id="cutoff_2nd" value="2nd"
                                   {{ old('cutoff') === '2nd' ? 'checked' : '' }}
                                   onchange="updatePreview()">
                            <div class="cutoff-card-body">
                                <strong>2nd Cut-off</strong>
                                <span>Coverage: 16–30/31</span>
                                <span class="text-muted" style="font-size:0.78rem;">Released on the 25th</span>
                            </div>
                        </label>

                    </div>
                    @error('cutoff')
                        <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Confirmation notice --}}
                <div class="alert alert-warning" id="confirmNotice" style="display:none;">
                    <div>
                        <strong>⚠ About to create:</strong>
                        <span id="confirmText"></span> — this will compute payroll for all active employees.
                    </div>
                </div>

                <div class="d-flex gap-2" style="margin-top:24px;">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        💰 Create &amp; Compute Payroll
                    </button>
                    <a href="{{ route('payroll.index') }}" class="btn btn-outline btn-lg">Cancel</a>
                </div>

            </form>
        </div>
    </div>

    {{-- ── Right: Preview Card + Rules ── --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Live Preview --}}
        <div class="card">
            <div class="card-header">
                <h3>🔍 Period Preview</h3>
            </div>
            <div class="card-body">
                <div id="previewBox" style="text-align:center; padding:12px 0;">
                    <div style="font-size:1.5rem; font-weight:700; color:var(--navy);" id="previewLabel">—</div>
                    <div class="text-muted" style="font-size:0.82rem; margin-top:6px;" id="previewSub">
                        Select a period above
                    </div>
                    <div style="margin-top:12px;" id="previewRelease"></div>
                </div>
            </div>
        </div>

        {{-- Computation Rules --}}
        <div class="card">
            <div class="card-header">
                <h3>📐 Computation Rules</h3>
            </div>
            <div class="card-body" style="font-size:0.84rem; color:var(--text-mid); line-height:1.8;">
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div>
                        <span class="fw-bold text-navy">Denominator</span><br>
                        Fixed at <strong>22 working days</strong> per cut-off
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:8px;">
                        <span class="fw-bold text-navy">Salary Earned</span><br>
                        Basic Monthly ÷ 2
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:8px;">
                        <span class="fw-bold text-navy">PERA Earned</span><br>
                        PERA Monthly ÷ 2
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:8px;">
                        <span class="fw-bold text-navy">Attendance Deduction</span><br>
                        Hits <em>leave credits first</em>;<br>
                        salary deducted only when credits are exhausted
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:8px;">
                        <span class="fw-bold text-navy">Tardiness / Undertime</span><br>
                        Converted via <strong>Table IV</strong> lookup<br>
                        (not direct formula)
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:8px;">
                        <span class="fw-bold text-navy">Withholding Tax</span><br>
                        Annualized (Jan–Dec)<br>
                        GSIS / PhilHealth / Pag-IBIG deducted from taxable income
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@section('scripts')
<style>
/* Cut-off selector cards */
.cutoff-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    background: white;
}
.cutoff-card:hover {
    border-color: var(--navy);
    background: var(--navy-light);
}
.cutoff-card--active {
    border-color: var(--navy);
    background: var(--navy-light);
}
.cutoff-card input[type="radio"] {
    width: auto;
    margin-top: 3px;
    flex-shrink: 0;
    accent-color: var(--navy);
}
.cutoff-card-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 0.88rem;
    line-height: 1.4;
}
.cutoff-card-body strong {
    font-size: 0.92rem;
    color: var(--navy);
}
</style>

<script>
const MONTHS = [
    '', 'January','February','March','April','May','June',
    'July','August','September','October','November','December'
];

function updatePreview() {
    const year   = document.getElementById('period_year').value;
    const month  = parseInt(document.getElementById('period_month').value);
    const cutoff = document.querySelector('input[name="cutoff"]:checked')?.value || '1st';

    const days    = cutoff === '1st' ? '1–15' : '16–30/31';
    const release = cutoff === '1st' ? '10th' : '25th';
    const label   = `${MONTHS[month]} ${days}, ${year}`;

    document.getElementById('previewLabel').textContent = label;
    document.getElementById('previewSub').textContent   = `${cutoff.toUpperCase()} cut-off`;
    document.getElementById('previewRelease').innerHTML =
        `<span class="badge badge-pending" style="font-size:0.78rem;">Release date: ${MONTHS[month]} ${release}, ${year}</span>`;

    // Confirmation notice
    document.getElementById('confirmText').textContent = label;
    document.getElementById('confirmNotice').style.display = 'flex';

    // Highlight active cut-off card
    document.querySelectorAll('.cutoff-card').forEach(card => {
        card.classList.remove('cutoff-card--active');
        if (card.querySelector('input[value="' + cutoff + '"]')) {
            card.classList.add('cutoff-card--active');
        }
    });
}

// Confirm before submit
document.getElementById('createForm').addEventListener('submit', function(e) {
    const year   = document.getElementById('period_year').value;
    const month  = parseInt(document.getElementById('period_month').value);
    const cutoff = document.querySelector('input[name="cutoff"]:checked')?.value || '1st';
    const days   = cutoff === '1st' ? '1–15' : '16–30/31';
    const label  = `${MONTHS[month]} ${days}, ${year}`;

    if (!confirm(`Create and compute payroll for:\n\n${label}\n\nThis will generate entries for all active employees. Continue?`)) {
        e.preventDefault();
    }
});

// Init on page load
updatePreview();
</script>
@endsection

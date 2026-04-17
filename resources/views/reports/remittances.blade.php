@extends('layouts.app')

@section('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   REMITTANCE DASHBOARD — Custom Styles
═══════════════════════════════════════════════════════════ */

/* ── Period Banner ────────────────────────────────────────── */
.rem-banner {
    background: var(--navy);
    border-radius: 12px;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.rem-banner-left h1 {
    color: #fff;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.2rem;
}
.rem-banner-left p {
    color: rgba(255,255,255,0.55);
    margin: 0;
    font-size: 0.875rem;
}
.rem-banner-form {
    display: flex;
    align-items: flex-end;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.rem-banner-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.rem-banner-form label {
    color: rgba(255,255,255,0.65);
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.rem-banner-form select {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    border-radius: 6px;
    padding: 0.45rem 0.75rem;
    font-size: 0.875rem;
    min-width: 130px;
    cursor: pointer;
}
.rem-banner-form select option { color: #111; background: #fff; }
.rem-banner-form select:focus { outline: none; border-color: var(--gold); }
.btn-apply {
    background: var(--gold);
    color: #1a1a2e;
    border: none;
    border-radius: 6px;
    padding: 0.5rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: opacity 0.15s;
}
.btn-apply:hover { opacity: 0.85; }

/* ── Period Pill ────────────────────────────────────────────── */
.period-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 20px;
    padding: 0.3rem 0.9rem;
    color: rgba(255,255,255,0.8);
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.5rem;
}
.period-label .cutoff-tag {
    background: var(--gold);
    color: #1a1a2e;
    border-radius: 10px;
    padding: 1px 8px;
    font-size: 0.7rem;
}

/* ── Active Preview Notice ────────────────────────────────── */
.preview-notice {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f2040 100%);
    border: 1px solid rgba(212,175,55,0.3);
    border-left: 4px solid var(--gold);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.preview-notice-info { display: flex; align-items: center; gap: 0.75rem; }
.preview-notice-icon {
    width: 36px; height: 36px;
    background: var(--gold);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.preview-notice h4 { color: #fff; margin: 0 0 2px; font-size: 0.9rem; }
.preview-notice p  { color: rgba(255,255,255,0.6); margin: 0; font-size: 0.8rem; }
.preview-stats { display: flex; gap: 1.5rem; flex-wrap: wrap; }
.preview-stat { text-align: right; }
.preview-stat .ps-label { color: rgba(255,255,255,0.5); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; }
.preview-stat .ps-value { color: #fff; font-size: 1rem; font-weight: 700; }
.preview-stat .ps-value.gold { color: var(--gold); }

/* ── Inline Preview Table Card ────────────────────────────── */
.preview-table-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.preview-table-card .ptc-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.85rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.preview-table-card .ptc-header h4 {
    margin: 0;
    font-size: 0.9rem;
    color: var(--navy);
    font-weight: 700;
}

/* ── Remittance Groups ────────────────────────────────────── */
.rem-groups { display: flex; flex-direction: column; gap: 1rem; }

.rem-group {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.rem-group-header {
    background: var(--navy);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.rem-group-header h3 {
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: 0.01em;
}
.group-icon { font-size: 1rem; opacity: 0.9; }

/* ── Report Rows ──────────────────────────────────────────── */
.rem-report-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.12s;
}
.rem-report-row:last-child { border-bottom: none; }
.rem-report-row:hover { background: #f8fafc; }
.rem-report-row.active-preview {
    background: #fffbeb;
    border-left: 3px solid var(--gold);
    padding-left: calc(1.25rem - 3px);
}

.rr-name {
    font-weight: 700;
    font-size: 0.875rem;
    color: #1a202c;
    margin-bottom: 3px;
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.rr-desc { font-size: 0.78rem; color: #64748b; line-height: 1.45; }
.rr-desc a { color: var(--navy); text-decoration: underline; }

/* Format Badges */
.badge-xlsx {
    display: inline-block;
    background: #dcfce7; color: #166534;
    font-size: 0.68rem; font-weight: 700;
    padding: 2px 8px; border-radius: 4px; letter-spacing: 0.05em;
}
.badge-csv {
    display: inline-block;
    background: #e0f2fe; color: #075985;
    font-size: 0.68rem; font-weight: 700;
    padding: 2px 8px; border-radius: 4px; letter-spacing: 0.05em;
}
.portal-note {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f0f9ff; border: 1px solid #bae6fd;
    border-radius: 4px; padding: 1px 7px;
    font-size: 0.7rem; color: #0369a1; font-weight: 600;
}

/* Action Buttons */
.rr-actions {
    display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end;
}
.btn-preview {
    display: inline-flex; align-items: center; gap: 4px;
    background: transparent;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    padding: 0.35rem 0.75rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600;
    text-decoration: none; transition: all 0.15s; white-space: nowrap;
}
.btn-preview:hover { border-color: var(--navy); color: var(--navy); background: #f1f5f9; }
.btn-preview.is-active { border-color: #d97706; color: #92400e; background: #fffbeb; }

.btn-dl {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--navy); color: #fff;
    border: none; padding: 0.35rem 0.75rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600;
    text-decoration: none; transition: opacity 0.15s; white-space: nowrap;
}
.btn-dl:hover { opacity: 0.85; color: #fff; }
.btn-dl-gold { background: var(--gold); color: #1a1a2e; }
.btn-dl-gold:hover { color: #1a1a2e; }
.btn-dl-csv { background: #0369a1; }

/* ── Responsive ───────────────────────────────────────────── */
@media (max-width: 900px) {
    .rem-banner { flex-direction: column; align-items: flex-start; }
    .rem-banner-form { width: 100%; }
    .rem-banner-form .form-group { flex: 1; min-width: 0; }
    .rem-banner-form select { width: 100%; }
    .rem-report-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .rr-actions { justify-content: flex-start; }
    .preview-stats { justify-content: flex-start; }
}
@media (max-width: 600px) {
    .rem-banner { padding: 1.25rem; }
    .rem-banner-form { flex-direction: column; }
    .btn-apply { width: 100%; text-align: center; }
    .rem-group-header { padding: 0.65rem 1rem; }
    .rem-report-row { padding: 0.85rem 1rem; }
    .rem-report-row.active-preview { padding-left: calc(1rem - 3px); }
    .preview-notice { flex-direction: column; }
    .preview-stats { flex-direction: row; gap: 1rem; }
    .preview-stat { text-align: left; }
}
</style>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════
     BANNER  (title + inline filter)
════════════════════════════════════════════════════════════ --}}
<div class="rem-banner">
    <div class="rem-banner-left">
        <h1>📑 Remittance Reports</h1>
        <p>Generate and download all payroll remittance schedules</p>
        <div class="period-label">
            <span>{{ $months[$month] }} {{ $year }}</span>
            @if ($cutoff !== 'both')
                <span class="cutoff-tag">{{ $cutoff === '1st' ? '1st Cut-off' : '2nd Cut-off' }}</span>
            @else
                <span class="cutoff-tag">Full Month</span>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="rem-banner-form">
        <div class="form-group">
            <label for="year">Year</label>
            <select name="year" id="year">
                @for ($y = $currentYear; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="form-group">
            <label for="month">Month</label>
            <select name="month" id="month">
                @foreach ($months as $num => $name)
                    <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="cutoff">Cut-off</label>
            <select name="cutoff" id="cutoff">
                <option value="both" {{ $cutoff === 'both' ? 'selected' : '' }}>Both (Full Month)</option>
                <option value="1st"  {{ $cutoff === '1st'  ? 'selected' : '' }}>1st Cut-off (1–15)</option>
                <option value="2nd"  {{ $cutoff === '2nd'  ? 'selected' : '' }}>2nd Cut-off (16–31)</option>
            </select>
        </div>
        <button type="submit" class="btn-apply">Apply Filter</button>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     INLINE PREVIEW PANEL
     Shown when a report's Preview is clicked (LBP / BTR /
     Provident / CARESS / MASS). The controller re-renders
     this same view with $activeReport + $reportRows set.
════════════════════════════════════════════════════════════ --}}
@if (isset($activeReport) && isset($reportRows) && $reportRows->count() > 0)

<div class="preview-notice">
    <div class="preview-notice-info">
        <div class="preview-notice-icon">👁</div>
        <div>
            <h4>
                Preview — {{ $months[$month] }} {{ $year }}
                @if ($cutoff !== 'both')
                    &mdash; {{ $cutoff === '1st' ? '1st Cut-off (1–15)' : '2nd Cut-off (16–31)' }}
                @endif
            </h4>
            <p>Showing deduction data currently in the system. Scroll down to download.</p>
        </div>
    </div>
    <div class="preview-stats">
        <div class="preview-stat">
            <div class="ps-label">Employees</div>
            <div class="ps-value">{{ number_format($employeeCount) }}</div>
        </div>
        <div class="preview-stat">
            <div class="ps-label">Total Amount</div>
            <div class="ps-value gold">₱{{ number_format($grandTotal, 2) }}</div>
        </div>
    </div>
</div>

<div class="preview-table-card">
    <div class="ptc-header">
        <h4>📋 Deduction Rows</h4>
        <span style="font-size:0.78rem; color:#64748b;">{{ $reportRows->count() }} record(s)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    @if ($activeReport === 'btr')
                        <th>Reason of Refund</th>
                    @endif
                    @if ($activeReport === 'caress_mortuary')
                        <th style="text-align:right;">Daily Rate</th>
                    @endif
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportRows as $i => $ded)
                    @php $emp = $ded->payrollEntry->employee; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ strtoupper($emp->last_name . ', ' . $emp->first_name) }}</td>
                        @if ($activeReport === 'btr')
                            <td>{{ $ded->deductionType->name ?? '—' }}</td>
                        @endif
                        @if ($activeReport === 'caress_mortuary')
                            @php $dr = round(($emp->semi_monthly_gross * 2) / 22, 2); @endphp
                            <td style="text-align:right;">₱{{ number_format($dr, 2) }}</td>
                        @endif
                        <td style="text-align:right;" class="fw-bold">₱{{ number_format($ded->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $activeReport === 'btr' ? 3 : ($activeReport === 'caress_mortuary' ? 3 : 2) }}"
                        class="fw-bold" style="text-align:right;">GRAND TOTAL</td>
                    <td class="fw-bold" style="text-align:right;">₱{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@elseif (isset($activeReport))
<div class="alert alert-warning" style="margin-bottom: 1.5rem;">
    No payroll data found for <strong>{{ $months[$month] }} {{ $year }}</strong>
    @if ($cutoff !== 'both')
        ({{ $cutoff === '1st' ? '1st' : '2nd' }} cut-off)
    @endif.
    Generate and compute payroll batches for this period first.
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     REMITTANCE GROUPS

     Preview behaviour per report type:
       GSIS / HDMF       → dedicated page (reports.gsis / reports.hdmf)
       CARESS / MASS / Provident / LBP / BTR
                         → re-renders THIS page with inline preview above
       PHIC / SSS        → CSV-only export, no in-app preview
════════════════════════════════════════════════════════════ --}}
<div class="rem-groups">

    {{-- ── GSIS ──────────────────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🏦</span>
            <h3>GSIS</h3>
        </div>
        <div class="rem-report-row">
            <div class="rr-info">
                <div class="rr-name">GSIS Remittance</div>
                <div class="rr-desc">Life/Retirement, Loans — by division subtotals</div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.gsis', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview">👁 Preview</a>
                <a href="{{ route('reports.gsis-summary', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-dl">⬇ Summary</a>
                <a href="{{ route('reports.gsis-detailed', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-dl btn-dl-gold">⬇ Detailed</a>
            </div>
        </div>
    </div>

    {{-- ── HDMF / Pag-IBIG ───────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🏧</span>
            <h3>HDMF / Pag-IBIG</h3>
        </div>
        <div class="rem-report-row">
            <div class="rr-info">
                <div class="rr-name">HDMF / Pag-IBIG Reports</div>
                <div class="rr-desc">P1 contributions, P2, MPL, Calamity Loan, Housing Loan (5 sheets)</div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.hdmf', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview">👁 Preview</a>
                <a href="{{ route('reports.hdmf-download', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-dl">⬇ Download All</a>
            </div>
        </div>
    </div>

    {{-- ── PhilHealth (PHIC) ─────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🏥</span>
            <h3>PhilHealth (PHIC)</h3>
        </div>
        <div class="rem-report-row">
            <div class="rr-info">
                <div class="rr-name">
                    PHIC Contributions
                    <span class="portal-note">Portal Upload</span>
                </div>
                <div class="rr-desc">
                    Extracted from the system. Generate PDF Billing and PHIC Remittance from the
                    <a href="https://www.philhealth.gov.ph" target="_blank" rel="noopener">PHIC Employer Portal</a>.
                </div>
            </div>
            <div><span class="badge-csv">CSV</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.phic-csv', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-dl btn-dl-csv">⬇ Download CSV</a>
            </div>
        </div>
    </div>

    {{-- ── CARESS IX ─────────────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🤝</span>
            <h3>CARESS IX</h3>
        </div>

        <div class="rem-report-row {{ isset($activeReport) && $activeReport === 'caress_union' ? 'active-preview' : '' }}">
            <div class="rr-info">
                <div class="rr-name">Union Dues</div>
                <div class="rr-desc">CARESS 9 monthly union dues — Payee: DOLE-CARESS9</div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.caress-union', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview {{ isset($activeReport) && $activeReport === 'caress_union' ? 'is-active' : '' }}">
                    👁 Preview
                </a>
                <a href="{{ route('reports.caress-union', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff,'download'=>1]) }}"
                   class="btn-dl">⬇ Download</a>
            </div>
        </div>

        <div class="rem-report-row {{ isset($activeReport) && $activeReport === 'caress_mortuary' ? 'active-preview' : '' }}">
            <div class="rr-info">
                <div class="rr-name">Mortuary Benefit</div>
                <div class="rr-desc">Death benefit schedule — Daily Rate × (0.25 + 0.25 + 0.50)</div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.caress-mortuary', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview {{ isset($activeReport) && $activeReport === 'caress_mortuary' ? 'is-active' : '' }}">
                    👁 Preview
                </a>
                <a href="{{ route('reports.caress-mortuary', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff,'download'=>1]) }}"
                   class="btn-dl">⬇ Download</a>
            </div>
        </div>
    </div>

    {{-- ── MASS ──────────────────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">📬</span>
            <h3>MASS</h3>
        </div>
        <div class="rem-report-row {{ isset($activeReport) && $activeReport === 'mass' ? 'active-preview' : '' }}">
            <div class="rr-info">
                <div class="rr-name">MASS Contribution</div>
                <div class="rr-desc">
                    Payee: Warren M. Miclat and Maria Teresa M. Cabance<br>
                    c/o Bureau of Labor Relations, Intramuros, Manila
                </div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.mass', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview {{ isset($activeReport) && $activeReport === 'mass' ? 'is-active' : '' }}">
                    👁 Preview
                </a>
                <a href="{{ route('reports.mass', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff,'download'=>1]) }}"
                   class="btn-dl">⬇ Download</a>
            </div>
        </div>
    </div>

    {{-- ── Provident Fund ────────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">💼</span>
            <h3>Provident Fund</h3>
        </div>
        <div class="rem-report-row {{ isset($activeReport) && $activeReport === 'provident_fund' ? 'active-preview' : '' }}">
            <div class="rr-info">
                <div class="rr-name">DOLE Provident Fund</div>
                <div class="rr-desc">
                    Payee: DOLEPFI Inc. — Account No. 2471-0431-01 · Land Bank of the Philippines
                </div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.provident-fund', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview {{ isset($activeReport) && $activeReport === 'provident_fund' ? 'is-active' : '' }}">
                    👁 Preview
                </a>
                <a href="{{ route('reports.provident-fund', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff,'download'=>1]) }}"
                   class="btn-dl">⬇ Download</a>
            </div>
        </div>
    </div>

    {{-- ── Land Bank Loan (LBP) ──────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🏛</span>
            <h3>Land Bank Loan (LBP)</h3>
        </div>
        <div class="rem-report-row {{ isset($activeReport) && $activeReport === 'lbp' ? 'active-preview' : '' }}">
            <div class="rr-info">
                <div class="rr-name">LBP Loan Remittance</div>
                <div class="rr-desc">Landbank of the Philippines loan deductions</div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.lbp-loan', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview {{ isset($activeReport) && $activeReport === 'lbp' ? 'is-active' : '' }}">
                    👁 Preview
                </a>
                <a href="{{ route('reports.lbp-loan', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff,'download'=>1]) }}"
                   class="btn-dl">⬇ Download</a>
            </div>
        </div>
    </div>

    {{-- ── Bureau of Treasury (BTR) ─────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🏢</span>
            <h3>Bureau of Treasury (BTR)</h3>
        </div>
        <div class="rem-report-row {{ isset($activeReport) && $activeReport === 'btr' ? 'active-preview' : '' }}">
            <div class="rr-info">
                <div class="rr-name">Revised Refund — Various Transactions</div>
                <div class="rr-desc">Withholding Tax and other refunds payable to Bureau of Treasury</div>
            </div>
            <div><span class="badge-xlsx">XLSX</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.btr-refund', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-preview {{ isset($activeReport) && $activeReport === 'btr' ? 'is-active' : '' }}">
                    👁 Preview
                </a>
                <a href="{{ route('reports.btr-refund', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff,'download'=>1]) }}"
                   class="btn-dl">⬇ Download</a>
            </div>
        </div>
    </div>

    {{-- ── SSS Voluntary ─────────────────────────────────────── --}}
    <div class="rem-group">
        <div class="rem-group-header">
            <span class="group-icon">🔒</span>
            <h3>SSS Voluntary</h3>
        </div>
        <div class="rem-report-row">
            <div class="rr-info">
                <div class="rr-name">
                    SSS Voluntary Contributions
                    <span class="portal-note">Portal Upload</span>
                </div>
                <div class="rr-desc">
                    Extracted from the system. Generate PDF Billing and SSS Remittance from the
                    <a href="https://www.sss.gov.ph" target="_blank" rel="noopener">SSS Employer Portal</a>.
                </div>
            </div>
            <div><span class="badge-csv">CSV</span></div>
            <div class="rr-actions">
                <a href="{{ route('reports.sss', ['year'=>$year,'month'=>$month,'cutoff'=>$cutoff]) }}"
                   class="btn-dl btn-dl-csv">⬇ Download CSV</a>
            </div>
        </div>
    </div>

</div>{{-- end .rem-groups --}}

@endsection

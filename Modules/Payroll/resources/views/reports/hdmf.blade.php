@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>HDMF / Pag-IBIG Remittance Reports</h1>
        <p class="text-muted">Generate all 5 HDMF remittance sheets in one Excel file for portal upload</p>
    </div>
</div>

{{-- ── Filter Card ──────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h3>Filter Period</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.hdmf') }}">
            <div class="d-flex gap-2 flex-wrap" style="align-items: flex-end;">

                <div class="form-group" style="flex: 0 0 140px;">
                    <label for="year">Year</label>
                    <select name="year" id="year" class="form-control">
                        @for ($y = $currentYear; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group" style="flex: 0 0 160px;">
                    <label for="month">Month</label>
                    <select name="month" id="month" class="form-control">
                        @foreach ($months as $num => $name)
                            <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="flex: 0 0 180px;">
                    <label for="cutoff">Cut-off</label>
                    <select name="cutoff" id="cutoff" class="form-control">
                        <option value="both" {{ $cutoff === 'both' ? 'selected' : '' }}>Both (Full Month)</option>
                        <option value="1st"  {{ $cutoff === '1st'  ? 'selected' : '' }}>1st Cut-off (1–15)</option>
                        <option value="2nd"  {{ $cutoff === '2nd'  ? 'selected' : '' }}>2nd Cut-off (16–31)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Preview</button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Summary Stats ────────────────────────────────────────────────────── --}}
<div class="stat-grid mb-4">
    <div class="stat-card">
        <div class="stat-label">P1 Contributors</div>
        <div class="stat-value">{{ number_format($employeeCount) }}</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Grand Total (All Sheets)</div>
        <div class="stat-value">₱{{ number_format($grandTotal, 2) }}</div>
    </div>
</div>

{{-- ── Per-Sheet Breakdown ──────────────────────────────────────────────── --}}
@if ($grandTotal > 0)
<div class="card mb-4">
    <div class="card-header">
        <h3>
            Sheet Breakdown — {{ $months[$month] }} {{ $year }}
            @if ($cutoff !== 'both')
                <span class="badge badge-draft" style="font-size:0.75rem; margin-left:6px;">
                    {{ $cutoff === '1st' ? '1st Cut-off' : '2nd Cut-off' }}
                </span>
            @endif
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Sheet</th>
                        <th>Program</th>
                        <th style="text-align:right;">Employee Count</th>
                        <th style="text-align:right;">EE Share Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sheets as $sheet)
                    <tr>
                        <td>{{ $sheet['label'] }}</td>
                        <td><code>{{ $sheet['program'] }}</code></td>
                        <td style="text-align:right;">{{ number_format($sheet['count']) }}</td>
                        <td style="text-align:right;" class="fw-bold">
                            @if ($sheet['total'] > 0)
                                ₱{{ number_format($sheet['total'], 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="fw-bold" style="text-align:right;">GRAND TOTAL</td>
                        <td class="fw-bold" style="text-align:right;">
                            ₱{{ number_format($grandTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    No HDMF payroll data found for <strong>{{ $months[$month] }} {{ $year }}</strong>
    @if ($cutoff !== 'both')
        ({{ $cutoff === '1st' ? '1st' : '2nd' }} cut-off)
    @endif.
    Generate and compute payroll batches for this period first.
</div>
@endif

{{-- ── Download ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3>Export</h3>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 1rem;">
            Downloads one Excel file containing all 5 sheets (P1, P2, MPL, CAL, Housing)
            formatted for direct HDMF portal upload. Amounts appear as plain numbers without ₱ symbols.
        </p>

        <div class="card mb-3" style="border-left: 4px solid var(--gold); background: #fffdf0;">
            <div class="card-body" style="padding: 0.75rem 1rem;">
                <p style="margin: 0; font-size: 0.85rem;" class="text-muted">
                    <strong>Sheet contents:</strong>
                    P1 — Pag-IBIG I regular contributions (code: F1) |
                    P2 — Modified Pag-IBIG II (code: M2) |
                    MPL — Multi-Purpose Loan repayments |
                    CAL — Calamity Loan repayments |
                    Housing — Housing Loan amortizations
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('reports.hdmf-download', ['year' => $year, 'month' => $month, 'cutoff' => $cutoff]) }}"
               class="btn btn-primary">
                ⬇ Download HDMF Reports (5 sheets)
            </a>
        </div>
        <p class="text-muted" style="margin-top: 0.75rem; font-size: 0.85rem;">
            Period: <strong>{{ $months[$month] }} {{ $year }}</strong>
            @if ($cutoff !== 'both')
                — <strong>{{ $cutoff === '1st' ? '1st Cut-off (1–15)' : '2nd Cut-off (16–31)' }}</strong>
            @endif
        </p>
    </div>
</div>
@endsection

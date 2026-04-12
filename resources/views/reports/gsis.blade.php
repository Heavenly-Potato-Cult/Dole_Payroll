@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>GSIS Remittance Reports</h1>
        <p class="text-muted">Generate Summary and Detailed GSIS remittance files for upload</p>
    </div>
</div>

{{-- ── Filter Card ──────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h3>Filter Period</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.gsis') }}">
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
        <div class="stat-label">Employees Included</div>
        <div class="stat-value">{{ number_format($employeeCount) }}</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Grand Total (GSIS)</div>
        <div class="stat-value">₱{{ number_format($grandTotal, 2) }}</div>
    </div>
</div>

{{-- ── Deduction Breakdown Table ────────────────────────────────────────── --}}
@if ($employeeCount > 0)
<div class="card mb-4">
    <div class="card-header">
        <h3>
            Deduction Breakdown — {{ $months[$month] }} {{ $year }}
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
                        <th>Code</th>
                        <th>GSIS Account</th>
                        <th style="text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($labelMap as $code => $label)
                        @if (isset($totals[$code]) && $totals[$code] > 0)
                        <tr>
                            <td><code>{{ $code }}</code></td>
                            <td>{{ $label }}</td>
                            <td style="text-align:right;" class="fw-bold">
                                ₱{{ number_format($totals[$code], 2) }}
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="fw-bold" style="text-align:right;">GRAND TOTAL</td>
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
    No payroll data found for <strong>{{ $months[$month] }} {{ $year }}</strong>
    @if ($cutoff !== 'both')
        ({{ $cutoff === '1st' ? '1st' : '2nd' }} cut-off)
    @endif.
    Generate and compute payroll batches for this period first.
</div>
@endif

{{-- ── Download Buttons ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3>Export</h3>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 1rem;">
            Both files are formatted for direct GSIS portal upload.
            Amounts appear as plain numbers without ₱ symbols.
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('reports.gsis-summary',  ['year' => $year, 'month' => $month, 'cutoff' => $cutoff]) }}"
               class="btn btn-primary">
                ⬇ Download Summary
            </a>
            <a href="{{ route('reports.gsis-detailed', ['year' => $year, 'month' => $month, 'cutoff' => $cutoff]) }}"
               class="btn btn-gold">
                ⬇ Download Detailed
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
{{-- Shared preview template for remittance reports --}}

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Employees</div>
        <div class="stat-value">{{ number_format($employeeCount ?? 0) }}</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Total Amount</div>
        <div class="stat-value">₱{{ number_format($grandTotal ?? 0, 2) }}</div>
    </div>
</div>

@if (isset($reportRows) && $reportRows->count() > 0)
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
                    @if (isset($showReason) && $showReason)
                        <th>Reason of Refund</th>
                    @endif
                    @if (isset($showDailyRate) && $showDailyRate)
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
                        @if (isset($showReason) && $showReason)
                            <td>{{ $ded->deductionType->name ?? '—' }}</td>
                        @endif
                        @if (isset($showDailyRate) && $showDailyRate)
                            @php $dr = round(($emp->semi_monthly_gross * 2) / 22, 2); @endphp
                            <td style="text-align:right;">₱{{ number_format($dr, 2) }}</td>
                        @endif
                        <td style="text-align:right;" class="fw-bold">₱{{ number_format($ded->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ (isset($showReason) && $showReason ? 1 : 0) + (isset($showDailyRate) && $showDailyRate ? 1 : 0) + 2 }}"
                        class="fw-bold" style="text-align:right;">GRAND TOTAL</td>
                    <td class="fw-bold" style="text-align:right;">₱{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@elseif (isset($reportRows))
<div class="alert alert-warning">
    No payroll data found for <strong>{{ $months[$month] }} {{ $year }}</strong>
    @if ($cutoff !== 'both')
        ({{ $cutoff === '1st' ? '1st' : '2nd' }} cut-off)
    @endif.
    Generate and compute payroll batches for this period first.
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3>{{ $title }}</h3>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 1rem;">
            {{ $description ?? '' }}
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route($downloadRoute, ['year' => $year, 'month' => $month, 'cutoff' => $cutoff]) }}"
               class="btn-dl">
                ⬇ Download {{ $format ?? 'XLSX' }}
            </a>
            <a href="{{ route($downloadRoute, ['year' => $year, 'month' => $month, 'cutoff' => $cutoff, 'download' => 1]) }}"
               class="btn-dl btn-dl-gold">
                ⬇ Download with Preview
            </a>
        </div>
    </div>
</div>

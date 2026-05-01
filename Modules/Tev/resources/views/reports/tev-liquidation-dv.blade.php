{{-- resources/views/reports/tev-liquidation-dv.blade.php
     Expects from ReportController@tevLiquidationDv:
       $tev  — TevRequest with employee, officeOrder, itineraryLines,
               certification, approvalLogs (with user)
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Liquidation DV – {{ $tev->tev_no }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 16mm 18mm 12mm 18mm;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, sans-serif;
        font-size: 8.5pt;
        color: #000;
        background: #fff;
    }

    .page {
        width: 100%;
        max-width: 174mm;
        margin: 0 auto;
    }

    /* ── HEADER ── */
    .header-wrap {
        text-align: center;
        margin-bottom: 4px;
        padding-bottom: 4px;
        border-bottom: 2.5px solid #1a1a8c;
    }
    .h-republic { font-size: 7.5pt; }
    .h-agency   { font-size: 13pt; font-weight: bold; letter-spacing: 0.01em; color: #1a1a8c; }
    .h-ro       { font-size: 9pt; font-weight: bold; color: #cc0000; }
    .h-address  { font-size: 7.5pt; }

    /* ── DOCUMENT TITLE ── */
    .doc-title-wrap {
        text-align: center;
        margin: 7px 0 5px;
    }
    .doc-title {
        font-size: 11pt;
        font-weight: bold;
        text-transform: uppercase;
        text-decoration: underline;
        letter-spacing: 0.06em;
    }
    .doc-subtitle {
        font-size: 8pt;
        font-style: italic;
        color: #333;
        margin-top: 2px;
    }

    /* ── DV META BAR ── */
    .dv-meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
        font-size: 8.5pt;
    }
    .dv-meta td {
        padding: 3px 7px;
        border: 1px solid #000;
        vertical-align: middle;
    }
    .dv-meta .lbl { font-weight: bold; white-space: nowrap; width: 110px; }
    .dv-meta .val { font-weight: bold; }

    /* ── PAYEE / ENTITY BLOCK ── */
    .payee-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
        margin-bottom: 0;
    }
    .payee-tbl td {
        border: 1px solid #000;
        padding: 4px 7px;
        vertical-align: middle;
    }
    .payee-lbl { font-weight: bold; width: 100px; }
    .payee-val { font-weight: bold; font-size: 9pt; }

    /* ── EXPENSE BREAKDOWN TABLE ── */
    .exp-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
        margin-top: 0;
    }
    .exp-tbl th {
        border: 1px solid #000;
        padding: 4px 5px;
        text-align: center;
        font-size: 8pt;
        font-weight: bold;
        background-color: #f0f0f0;
        vertical-align: middle;
    }
    .exp-tbl td {
        border: 1px solid #000;
        padding: 3px 6px;
        vertical-align: middle;
    }
    .td-l { text-align: left !important; }
    .td-r { text-align: right !important; }
    .td-c { text-align: center !important; }
    .row-subtotal td {
        background: #f8f8f8;
        font-weight: bold;
        font-size: 8pt;
    }
    .row-total td {
        background: #f0f0f0;
        font-weight: bold;
        font-size: 9pt;
    }

    /* ── RECONCILIATION BLOCK ── */
    .recon-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
        margin-top: 0;
    }
    .recon-tbl td {
        border: 1px solid #000;
        padding: 4px 8px;
        vertical-align: middle;
    }
    .recon-lbl { font-weight: bold; width: 200px; }
    .recon-val { font-weight: bold; text-align: right; width: 120px; }
    .recon-balance-refund  td { background: #FFF8E1; }
    .recon-balance-claim   td { background: #E8F5E9; }
    .recon-balance-settled td { background: #F3E5F5; }

    /* ── CERTIFICATION STATEMENT ── */
    .cert-box {
        border: 1px solid #000;
        border-top: none;
        padding: 7px 10px;
        font-size: 8pt;
        font-style: italic;
        line-height: 1.6;
        text-align: justify;
        background: #fafafa;
    }

    /* ── APPROVAL CHAIN ── */
    .chain-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
        margin-top: 0;
    }
    .chain-tbl td {
        border: 1px solid #000;
        padding: 4px 6px;
        vertical-align: top;
        width: 25%;
    }
    .chain-role  { font-weight: bold; font-size: 7.5pt; text-transform: uppercase; color: #333; }
    .chain-name  { font-weight: bold; font-size: 9pt;   text-transform: uppercase; margin-top: 22px; border-top: 1px solid #000; padding-top: 3px; }
    .chain-title { font-size: 7.5pt; color: #444; }
    .chain-date  { font-size: 7pt;   color: #666; margin-top: 2px; }

    /* ── FOOTER ── */
    .footer {
        margin-top: 7px;
        font-size: 6.5pt;
        color: #666;
        border-top: 1px solid #ccc;
        padding-top: 3px;
        display: flex;
        justify-content: space-between;
    }

    /* ── PRINT BUTTON (screen only) ── */
    .print-btn-wrap {
        text-align: right;
        margin-bottom: 10px;
    }
    .print-btn {
        padding: 6px 18px;
        background: #0F1B4C;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 9pt;
        cursor: pointer;
    }
    @media print {
        .print-btn-wrap { display: none; }
    }
</style>
</head>
<body>
<div class="page">

@php
    $emp   = $tev->employee;
    $oo    = $tev->officeOrder;
    $cert  = $tev->certification;
    $lines = $tev->itineraryLines ?? collect();
    $logs  = $tev->approvalLogs  ?? collect();

    /* ── Employee name ── */
    $empName = $emp
        ? strtoupper(trim(
            $emp->last_name . ', ' . $emp->first_name . ' ' .
            ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')
          ))
        : '—';

    /* ── Amounts ── */
    $advanceAmount = (float) ($tev->cash_advance_amount ?? $tev->grand_total);
    $actualAmount  = (float) ($tev->grand_total);          // itinerary-computed total
    $balanceDue    = (float) ($tev->balance_due ?? round($advanceAmount - $actualAmount, 2));
    $totalTransport = (float) $tev->total_transportation;
    $totalPerDiem   = (float) $tev->total_per_diem;

    /* ── Balance label ── */
    if ($balanceDue > 0) {
        $balanceLabel = 'Amount to Refund (Employee Owes)';
        $balanceClass = 'recon-balance-refund';
    } elseif ($balanceDue < 0) {
        $balanceLabel = 'Additional Amount to Claim (DOLE Owes Employee)';
        $balanceClass = 'recon-balance-claim';
    } else {
        $balanceLabel = 'No Balance — Fully Settled';
        $balanceClass = 'recon-balance-settled';
    }

    /* ── Approval chain: find each step from the logs ── */
    $findLog = fn(string $step) => $logs->firstWhere('step', $step);

    $logAccountant = $findLog('accountant_certified');
    $logRd         = $findLog('rd_approved');
    $logCashier    = $findLog('cashier_released');
    $logLiquidated = $findLog('liquidated');

    /* ── Approving officer (from Office Order) ── */
    $approverName = optional($oo)->approving_officer_name    ?? '';
    $approverPos  = optional($oo)->approving_officer_position ?? 'Assistant Regional Director';

    /* ── DV reference info ── */
    $dvDate = $logLiquidated
        ? $logLiquidated->performed_at->format('F d, Y')
        : now()->format('F d, Y');

    $officeDivision = optional(optional($emp)->division)->name ?? 'TSSD';
@endphp

{{-- ── PRINT BUTTON (screen only) ── --}}
<div class="print-btn-wrap">
    <button class="print-btn" onclick="window.print()">🖨 Print / Save as PDF</button>
</div>

{{-- ── HEADER ── --}}
<div class="header-wrap">
    <div class="h-republic">Republic of the Philippines</div>
    <div class="h-agency">DEPARTMENT OF LABOR AND EMPLOYMENT</div>
    <div class="h-ro">Regional Office No. 9</div>
    <div class="h-address">Cortez Building, Dr. Evangelista Street, Sta. Catalina, Zamboanga City</div>
</div>

{{-- ── TITLE ── --}}
<div class="doc-title-wrap">
    <div class="doc-title">Travel Expense Voucher — Liquidation Report</div>
    <div class="doc-subtitle">Cash Advance Liquidation | TEV No.: {{ $tev->tev_no }}</div>
</div>

{{-- ── DV META: TEV No., Date, Fund Cluster ── --}}
<table class="dv-meta">
    <tr>
        <td class="lbl">TEV No.:</td>
        <td class="val" style="width:40%;">{{ $tev->tev_no }}</td>
        <td class="lbl" style="width:100px;">Date:</td>
        <td class="val">{{ $dvDate }}</td>
    </tr>
    <tr>
        <td class="lbl">Office Order No.:</td>
        <td class="val">{{ optional($oo)->office_order_no ?? '—' }}</td>
        <td class="lbl">Fund Cluster:</td>
        <td class="val">01 — Regular</td>
    </tr>
    <tr>
        <td class="lbl">Travel Period:</td>
        <td class="val">
            {{ optional($tev->travel_date_start)->format('M d') }}
            – {{ optional($tev->travel_date_end)->format('M d, Y') }}
        </td>
        <td class="lbl">Division:</td>
        <td class="val">{{ strtoupper($officeDivision) }}</td>
    </tr>
</table>

{{-- ── PAYEE INFO ── --}}
<table class="payee-tbl">
    <tr>
        <td class="payee-lbl">Payee / Employee:</td>
        <td class="payee-val" style="width:55%;">{{ $empName }}</td>
        <td class="payee-lbl" style="width:90px;">Employee No.:</td>
        <td class="payee-val">{{ optional($emp)->employee_no ?? '—' }}</td>
    </tr>
    <tr>
        <td class="payee-lbl">Position:</td>
        <td colspan="3">{{ optional($emp)->position_title ?? '—' }}</td>
    </tr>
    <tr>
        <td class="payee-lbl">Purpose:</td>
        <td colspan="3">{{ $tev->purpose }}{{ $tev->destination ? ' — ' . $tev->destination : '' }}</td>
    </tr>
</table>

{{-- ── ITINERARY EXPENSE BREAKDOWN ── --}}
<table class="exp-tbl">
    <thead>
        <tr>
            <th style="width:75px;">Date</th>
            <th>From</th>
            <th>To</th>
            <th style="width:80px;">Mode</th>
            <th style="width:80px;">Transportation</th>
            <th style="width:75px;">Per Diem</th>
            <th style="width:75px;">Line Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($lines as $line)
        @php
            $lineTotal = (float)$line->transportation_cost + (float)$line->per_diem_amount;
        @endphp
        <tr>
            <td class="td-c">{{ $line->travel_date->format('d M Y') }}</td>
            <td class="td-l">{{ $line->origin }}</td>
            <td class="td-l">{{ $line->destination }}</td>
            <td class="td-c" style="font-size:7.5pt;">{{ strtoupper($line->mode_of_transport ?? '—') }}</td>
            <td class="td-r">
                {{ (float)$line->transportation_cost > 0 ? number_format($line->transportation_cost, 2) : '—' }}
            </td>
            <td class="td-r">
                {{ (float)$line->per_diem_amount > 0 ? number_format($line->per_diem_amount, 2) : '—' }}
            </td>
            <td class="td-r">{{ $lineTotal > 0 ? number_format($lineTotal, 2) : '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="td-c" style="padding:10px; font-style:italic; color:#666;">
                No itinerary lines on record.
            </td>
        </tr>
        @endforelse

        {{-- Buffer rows ── --}}
        @for ($i = 0; $i < max(0, 2 - $lines->count()); $i++)
        <tr><td style="height:16px;">&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        @endfor

        {{-- Subtotals ── --}}
        <tr class="row-subtotal">
            <td colspan="4" class="td-r" style="letter-spacing:0.04em;">Subtotals:</td>
            <td class="td-r">{{ $totalTransport > 0 ? number_format($totalTransport, 2) : '—' }}</td>
            <td class="td-r">{{ $totalPerDiem   > 0 ? number_format($totalPerDiem, 2)   : '—' }}</td>
            <td class="td-r">{{ number_format($totalTransport + $totalPerDiem, 2) }}</td>
        </tr>

        {{-- Grand total (per itinerary) ── --}}
        <tr class="row-total">
            <td colspan="6" class="td-r" style="letter-spacing:0.05em; padding-right:10px;">
                TOTAL CLAIMABLE AMOUNT (per Itinerary):
            </td>
            <td class="td-r" style="font-size:9.5pt;">
                ₱{{ number_format($totalTransport + $totalPerDiem, 2) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- ── CASH ADVANCE RECONCILIATION ── --}}
<table class="recon-tbl">
    <tr>
        <td class="recon-lbl">Cash Advance Released:</td>
        <td class="recon-val">₱{{ number_format($advanceAmount, 2) }}</td>
        <td style="font-size:7.5pt; color:#555; padding-left:12px;">
            Released by Cashier after RD/ARD approval.
        </td>
    </tr>
    <tr>
        <td class="recon-lbl">Total Actual Expenses (per Itinerary):</td>
        <td class="recon-val">₱{{ number_format($actualAmount, 2) }}</td>
        <td style="font-size:7.5pt; color:#555; padding-left:12px;">
            Sum of transportation costs and per diem allowances.
        </td>
    </tr>
    <tr class="{{ $balanceClass }}">
        <td class="recon-lbl" style="font-size:9pt;">{{ $balanceLabel }}:</td>
        <td class="recon-val" style="font-size:10pt;">
            ₱{{ number_format(abs($balanceDue), 2) }}
        </td>
        <td style="font-size:7.5pt; color:#555; padding-left:12px;">
            @if ($balanceDue > 0)
                Employee must refund this amount to the Cashier.
            @elseif ($balanceDue < 0)
                Cashier must release additional payment to the employee.
            @else
                Cash advance exactly covers actual expenses.
            @endif
        </td>
    </tr>
</table>

{{-- ── CERTIFICATION STATEMENT ── --}}
<div class="cert-box">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    I hereby certify that this liquidation report is correct and complete. The travel
    expenses claimed herein were actually and necessarily incurred in the performance of
    official duties, pursuant to Office Order No. {{ optional($oo)->office_order_no ?? '____' }}.
    I am fully aware that any false statement or misrepresentation is punishable by law.
</div>

{{-- ── APPROVAL CHAIN ── --}}
<table class="chain-tbl">
    <tr>
        {{-- Prepared by: Employee ── --}}
        <td>
            <div class="chain-role">Prepared by (Employee):</div>
            <div class="chain-name">{{ $empName }}</div>
            <div class="chain-title">{{ optional($emp)->position_title ?? '' }}</div>
            <div class="chain-date">
                @if ($logCashier)
                    {{ $logCashier->performed_at->format('M d, Y') }}
                @else
                    &nbsp;
                @endif
            </div>
        </td>

        {{-- Certified by: Accountant ── --}}
        <td>
            <div class="chain-role">Certified Correct (Accountant):</div>
            <div class="chain-name">
                @if ($logAccountant && $logAccountant->user)
                    {{ strtoupper($logAccountant->user->name) }}
                @else
                    &nbsp;
                @endif
            </div>
            <div class="chain-title">Accountant</div>
            <div class="chain-date">
                @if ($logAccountant)
                    {{ $logAccountant->performed_at->format('M d, Y') }}
                @else
                    &nbsp;
                @endif
            </div>
        </td>

        {{-- Approved by: RD/ARD ── --}}
        <td>
            <div class="chain-role">Approved by (RD / ARD):</div>
            <div class="chain-name">
                @if ($logRd && $logRd->user)
                    {{ strtoupper($logRd->user->name) }}
                @elseif ($approverName)
                    {{ strtoupper($approverName) }}
                @else
                    &nbsp;
                @endif
            </div>
            <div class="chain-title">{{ $approverPos }}</div>
            <div class="chain-date">
                @if ($logRd)
                    {{ $logRd->performed_at->format('M d, Y') }}
                @else
                    &nbsp;
                @endif
            </div>
        </td>

        {{-- Settled by: Cashier ── --}}
        <td>
            <div class="chain-role">Liquidation Approved (Cashier):</div>
            <div class="chain-name">
                @if ($logLiquidated && $logLiquidated->user)
                    {{ strtoupper($logLiquidated->user->name) }}
                @else
                    &nbsp;
                @endif
            </div>
            <div class="chain-title">Cashier</div>
            <div class="chain-date">
                @if ($logLiquidated)
                    {{ $logLiquidated->performed_at->format('M d, Y') }}
                @else
                    &nbsp;
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ── FOOTER ── --}}
<div class="footer">
    <span>{{ $tev->tev_no }} &nbsp;|&nbsp; Cash Advance Liquidation &nbsp;|&nbsp; DOLE RO9</span>
    <span>Generated: {{ now()->format('F j, Y \a\t h:i A') }}</span>
</div>

</div>{{-- end .page --}}
</body>
</html>
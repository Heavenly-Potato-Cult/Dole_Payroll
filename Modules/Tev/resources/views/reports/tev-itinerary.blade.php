{{-- resources/views/reports/tev-itinerary.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Itinerary of Travel – {{ $tev->tev_no }}</title>
<style>
    /* ── SCREEN WRAPPER ── */
    body {
        margin: 0;
        padding: 0;
        background: #e8eaf0;
        font-family: Arial, sans-serif;
    }

    .screen-toolbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: 44px;
        background: #0F1B4C;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 100;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }
    .screen-toolbar .doc-label {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .screen-toolbar .tev-ref {
        color: #aab4d4;
        font-size: 11px;
        margin-left: 10px;
    }
    .print-btn {
        background: #F9A825;
        color: #0F1B4C;
        border: none;
        padding: 7px 18px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .print-btn:hover { background: #f0a000; }

    .page-canvas {
        padding: 60px 20px 40px;
        display: flex;
        justify-content: center;
    }

    /* ── DOCUMENT SHEET ── */
    .sheet {
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        padding: 18mm 18mm 14mm 18mm;
        box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        box-sizing: border-box;
        font-size: 9pt;
        color: #000;
    }

    /* ── HEADER ── */
    .header-tbl { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .header-tbl td { padding: 0 4px; vertical-align: middle; }
    .logo-cell { width: 62px; text-align: center; }
    .logo-cell img { width: 54px; height: 54px; object-fit: contain; }
    .logo-right-cell { width: 100px; text-align: center; }
    .logo-right-cell img { width: 96px; height: 48px; object-fit: contain; }
    .header-center { text-align: center; vertical-align: middle; }
    .h-republic { font-size: 7.5pt; }
    .h-agency   { font-size: 13pt; font-weight: bold; color: #1a1a8c; letter-spacing: 0.02em; }
    .h-ro       { font-size: 9.5pt; font-weight: bold; color: #cc0000; }
    .h-address  { font-size: 7.5pt; }

    /* ── TITLE ── */
    .doc-title-wrap {
        text-align: center;
        margin: 8px 0 6px;
        border-top: 2px solid #1a1a8c;
        border-bottom: 1px solid #1a1a8c;
        padding: 5px 0 4px;
    }
    .doc-title    { font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; }
    .doc-subtitle { font-size: 9pt;  font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; color: #333; margin-top: 1px; }

    /* ── META TABLE ── */
    .meta-tbl { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt; }
    .meta-tbl td { padding: 2px 4px; vertical-align: top; }
    .meta-lbl { font-weight: bold; white-space: nowrap; width: 125px; }

    /* ── ITINERARY TABLE ── */
    .itin-tbl { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 8px; }
    .itin-tbl th {
        border: 1px solid #000;
        padding: 4px 3px;
        text-align: center;
        font-size: 7.5pt;
        font-weight: bold;
        background-color: #f0f0f0;
        vertical-align: middle;
    }
    .itin-tbl td {
        border: 1px solid #000;
        padding: 3px 4px;
        text-align: center;
        vertical-align: middle;
    }
    .td-l { text-align: left !important; }
    .td-r { text-align: right !important; }

    .row-nf td {
        text-align: center; font-weight: bold; font-size: 8pt;
        padding: 4px; border: 1px solid #000; background-color: #f8f8f8;
    }
    .row-gt td {
        font-weight: bold; font-size: 9pt;
        border: 1px solid #000; padding: 4px 5px; background-color: #f0f0f0;
    }

    /* ── BOTTOM BLOCK ── */
    .bottom-wrap    { width: 100%; margin-top: 14px; display: table; }
    .certify-col    { display: table-cell; width: 44%; vertical-align: top; padding-right: 12px; border-right: 1px solid #000; }
    .sig-col        { display: table-cell; width: 56%; vertical-align: top; padding-left: 12px; }
    .certify-text   { font-size: 8.5pt; line-height: 1.65; text-align: justify; border: 1px solid #000; padding: 8px 10px; }
    .certify-sig-wrap  { margin-top: 22px; }
    .certify-sig-line  { border-top: 1px solid #000; padding-top: 3px; font-weight: bold; font-size: 9pt; text-transform: uppercase; }
    .sig-block      { margin-bottom: 14px; }
    .sig-label      { font-size: 8pt; color: #333; margin-bottom: 22px; display: block; }
    .sig-name-line  { border-top: 1px solid #000; padding-top: 3px; font-weight: bold; text-transform: uppercase; font-size: 9.5pt; text-align: center; }
    .sig-pos        { font-size: 8pt; text-align: center; color: #333; }

    /* ── FOOTER ── */
    .doc-footer {
        margin-top: 10px; font-size: 6.5pt; color: #666;
        border-top: 1px solid #ccc; padding-top: 3px;
        display: flex; justify-content: space-between;
    }

    /* ── PRINT OVERRIDES ── */
    @media print {
        body { background: #fff; }
        .screen-toolbar { display: none; }
        .page-canvas { padding: 0; display: block; }
        .sheet {
            width: 100%; min-height: unset;
            padding: 16mm 18mm 12mm 18mm;
            box-shadow: none; margin: 0;
        }
        @page { size: A4 portrait; margin: 0; }
    }
</style>
</head>
<body>

{{-- ── SCREEN TOOLBAR ── --}}
<div class="screen-toolbar">
    <div>
        <span class="doc-label">Itinerary of Travel (Appendix A)</span>
        <span class="tev-ref">{{ $tev->tev_no }}</span>
    </div>
    <button class="print-btn" onclick="window.print()">🖨 Print / Save as PDF</button>
</div>

<div class="page-canvas">
<div class="sheet">

@php
    $emp   = $tev->employee;
    $oo    = $tev->officeOrder;
    $lines = $tev->itineraryLines ?? collect();

    $empName = $emp
        ? strtoupper(trim(
            $emp->last_name . ', ' . $emp->first_name . ' ' .
            ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')
          ))
        : '—';

    $totalTransport = (float) $tev->total_transportation;
    $totalPerDiem   = (float) $tev->total_per_diem;
    $grandTotal     = $totalTransport + $totalPerDiem;

    $notedByName = optional($oo)->approving_officer_name    ?? '';
    $notedByPos  = optional($oo)->approving_officer_position ?? 'Assistant Regional Director';
@endphp

{{-- ── HEADER ── --}}
<table class="header-tbl">
    <tr>
        <td class="logo-cell">
            <img src="{{ asset('assets/img/dole_logo.png') }}" alt="DOLE">
        </td>
        <td class="header-center">
            <div class="h-republic">Republic of the Philippines</div>
            <div class="h-agency">DEPARTMENT OF LABOR AND EMPLOYMENT</div>
            <div class="h-ro">Regional Office No. 9</div>
            <div class="h-address">Cortez Building, Dr. Evangelista Street</div>
            <div class="h-address">Sta. Catalina, Zamboanga City</div>
        </td>
        <td class="logo-right-cell">
            <img src="{{ asset('assets/img/bagong_pilipinas_logo.png') }}" alt="Bagong Pilipinas">
        </td>
    </tr>
</table>

{{-- ── TITLE ── --}}
<div class="doc-title-wrap">
    <div class="doc-title">Itinerary of Travel</div>
    <div class="doc-subtitle">(Appendix A)</div>
</div>

{{-- ── EMPLOYEE META ── --}}
<table class="meta-tbl">
    <tr>
        <td class="meta-lbl">Name:</td>
        <td style="font-weight:bold;">{{ $empName }}</td>
    </tr>
    <tr>
        <td class="meta-lbl">Position:</td>
        <td>{{ optional($emp)->position_title ?? '—' }}</td>
    </tr>
    <tr>
        <td class="meta-lbl">Official Station:</td>
        <td>{{ optional($emp)->official_station ?? 'DOLE 9 - TSSD, ZAMBOANGA CITY' }}</td>
    </tr>
    <tr>
        <td class="meta-lbl">Purpose:</td>
        <td>{{ $tev->purpose }}</td>
    </tr>
    @if ($tev->destination)
    <tr>
        <td class="meta-lbl"></td>
        <td>{{ $tev->destination }}</td>
    </tr>
    @endif
</table>

{{-- ── ITINERARY TABLE ── --}}
<table class="itin-tbl">
    <thead>
        <tr>
            <th rowspan="2" style="width:68px;">Date</th>
            <th colspan="2">Places to be Visited</th>
            <th colspan="2">Time</th>
            <th rowspan="2" style="width:70px;">Means of<br>Transportation</th>
            <th colspan="2">Allowable Expenses</th>
            <th rowspan="2" style="width:64px;">Total<br>Amount</th>
        </tr>
        <tr>
            <th style="width:98px;">From</th>
            <th style="width:98px;">To</th>
            <th style="width:50px;">Departure</th>
            <th style="width:50px;">Arrival</th>
            <th style="width:68px;">Transportation</th>
            <th style="width:68px;">Allowance</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($lines as $line)
        @php $lineTotal = (float)$line->transportation_cost + (float)$line->per_diem_amount; @endphp
        <tr>
            <td>{{ $line->travel_date->format('d M Y') }}</td>
            <td class="td-l">{{ $line->origin }}</td>
            <td class="td-l">{{ $line->destination }}</td>
            <td>{{ $line->departure_time ? \Carbon\Carbon::parse($line->departure_time)->format('h:iA') : '' }}</td>
            <td>{{ $line->arrival_time   ? \Carbon\Carbon::parse($line->arrival_time)->format('h:iA')   : '' }}</td>
            <td>{{ strtoupper($line->mode_of_transport ?? '') }}</td>
            <td class="td-r">{{ (float)$line->transportation_cost > 0 ? number_format($line->transportation_cost, 2) : '' }}</td>
            <td class="td-r">{{ (float)$line->per_diem_amount > 0     ? number_format($line->per_diem_amount, 2)     : '' }}</td>
            <td class="td-r">{{ $lineTotal > 0 ? number_format($lineTotal, 2) : '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" style="text-align:center; padding:10px; font-style:italic; color:#666;">No itinerary lines.</td>
        </tr>
        @endforelse

        @for ($i = 0; $i < max(0, 2 - $lines->count()); $i++)
        <tr>
            <td style="height:18px;">&nbsp;</td>
            <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            <td class="td-r">-</td>
        </tr>
        @endfor

        <tr class="row-nf">
            <td colspan="6" style="text-align:center; letter-spacing:0.05em;">NOTHING FOLLOWS</td>
            <td></td><td></td><td class="td-r">-</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="row-gt">
            <td colspan="6" style="text-align:center; letter-spacing:0.05em;">GRAND TOTAL</td>
            <td class="td-r">{{ $totalTransport > 0 ? number_format($totalTransport, 2) : '' }}</td>
            <td class="td-r">{{ $totalPerDiem   > 0 ? number_format($totalPerDiem, 2)   : '' }}</td>
            <td class="td-r">{{ number_format($grandTotal, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ── BOTTOM: CERTIFY LEFT + SIGNATURES RIGHT ── --}}
<div class="bottom-wrap">
    <div class="certify-col">
        <div class="certify-text">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I CERTIFY that I have reviewed the foregoing itinerary.
            The travel is necessary to the service. The period covered is
            reasonable. The expenses claimed are proper.
        </div>
        <div class="certify-sig-wrap">
            <div class="certify-sig-line">{{ strtoupper($notedByName) }}</div>
            <div class="sig-pos">{{ $notedByPos }}</div>
        </div>
    </div>
    <div class="sig-col">
        <div class="sig-block">
            <span class="sig-label">Prepared by:</span>
            <div class="sig-name-line">{{ $empName }}</div>
            <div class="sig-pos">{{ optional($emp)->position_title ?? '' }}</div>
        </div>
        <div class="sig-block" style="margin-top:20px;">
            <span class="sig-label">Approved by:</span>
            <div class="sig-name-line">&nbsp;</div>
            <div class="sig-pos">Assistant Regional Director</div>
        </div>
    </div>
</div>

{{-- ── FOOTER ── --}}
<div class="doc-footer">
    <span>{{ $tev->tev_no }}</span>
    <span>Generated: {{ now()->format('F j, Y \a\t h:i A') }}</span>
</div>

</div>{{-- end .sheet --}}
</div>{{-- end .page-canvas --}}
</body>
</html>
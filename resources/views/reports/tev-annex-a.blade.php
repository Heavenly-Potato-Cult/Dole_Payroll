{{-- resources/views/reports/tev-annex-a.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Annex A – {{ $tev->tev_no }}</title>
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
        letter-spacing: 0.01em;
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
        padding: 20mm 20mm 16mm 20mm;
        box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        box-sizing: border-box;
        position: relative;
    }

    /* ── ANNEX A label ── */
    .annex-label {
        text-align: right;
        font-style: italic;
        font-size: 9.5pt;
        font-weight: bold;
        margin-bottom: 3px;
    }

    /* ── HEADER ── */
    .header-wrap {
        text-align: center;
        margin-bottom: 5px;
        padding-bottom: 4px;
        border-bottom: 2px solid #1a1a8c;
    }
    .h-republic { font-size: 8pt; }
    .h-agency   { font-size: 13pt; font-weight: bold; letter-spacing: 0.01em; }
    .h-ro       { font-size: 9pt; }
    .h-address  { font-size: 8pt; }

    /* ── TITLE ── */
    .doc-title {
        text-align: center;
        font-size: 11pt;
        font-weight: bold;
        text-transform: uppercase;
        text-decoration: underline;
        margin: 7px 0 1px;
        letter-spacing: 0.04em;
    }
    .doc-subtitle {
        text-align: center;
        font-size: 8.5pt;
        font-style: italic;
        margin-bottom: 8px;
        color: #333;
    }

    /* ── EMPLOYEE INFO TABLE ── */
    .emp-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .emp-tbl td {
        border: 1px solid #000;
        padding: 4px 7px;
        vertical-align: middle;
    }
    .e-lbl { width: 130px; }
    .e-val  { font-weight: bold; }

    /* ── EXPENSES TABLE ── */
    .exp-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .exp-tbl th {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        font-weight: bold;
        vertical-align: middle;
        font-size: 8.5pt;
        background-color: #f0f0f0;
    }
    .exp-tbl td {
        border: 1px solid #000;
        padding: 4px 7px;
        vertical-align: middle;
        text-align: center;
    }
    .td-l { text-align: left !important; }
    .td-r { text-align: right !important; }
    .row-total td { font-weight: bold; font-size: 9.5pt; background-color: #f0f0f0; }

    /* ── PURPOSE ── */
    .purpose-tbl { width: 100%; border-collapse: collapse; font-size: 9pt; }
    .purpose-tbl td { border: 1px solid #000; padding: 5px 8px; vertical-align: top; }

    /* ── CERTIFICATION TEXT ── */
    .cert-italic {
        font-size: 8.5pt;
        font-style: italic;
        line-height: 1.6;
        text-align: justify;
        padding: 7px 9px;
        border: 1px solid #000;
        border-top: none;
        background-color: #fafafa;
    }

    /* ── SIGNATURE TABLE ── */
    .sig-tbl { width: 100%; border-collapse: collapse; font-size: 9pt; }
    .sig-tbl td { border: 1px solid #000; padding: 4px 8px; vertical-align: middle; }
    .sig-section-lbl { font-weight: bold; font-size: 8.5pt; }
    .sig-name-bold { font-weight: bold; font-size: 10pt; text-transform: uppercase; }
    .sig-role { font-size: 8pt; color: #333; }

    /* ── FOOTER ── */
    .doc-footer {
        margin-top: 8px;
        font-size: 6.5pt;
        color: #666;
        border-top: 1px solid #ccc;
        padding-top: 3px;
        display: flex;
        justify-content: space-between;
    }

    /* ── PRINT OVERRIDES ── */
    @media print {
        body { background: #fff; }
        .screen-toolbar { display: none; }
        .page-canvas { padding: 0; display: block; }
        .sheet {
            width: 100%;
            min-height: unset;
            padding: 16mm 18mm 12mm 18mm;
            box-shadow: none;
            margin: 0;
        }
        @page { size: A4 portrait; margin: 0; }
    }
</style>
</head>
<body>

{{-- ── SCREEN TOOLBAR ── --}}
<div class="screen-toolbar">
    <div>
        <span class="doc-label">Annex A — Certification of Expenses Not Requiring Receipts</span>
        <span class="tev-ref">{{ $tev->tev_no }}</span>
    </div>
    <button class="print-btn" onclick="window.print()">🖨 Print / Save as PDF</button>
</div>

<div class="page-canvas">
<div class="sheet">

@php
    $emp   = $tev->employee;
    $oo    = $tev->officeOrder;
    $cert  = $tev->certification;
    $lines = $tev->itineraryLines ?? collect();

    $empName = $emp
        ? strtoupper(trim(
            $emp->last_name . ', ' . $emp->first_name . ' ' .
            ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')
          ))
        : '—';

    $expRows = $lines->filter(function($l) {
        return (float)$l->transportation_cost > 0 || (float)$l->per_diem_amount > 0;
    });

    $annexATotal = (float)(optional($cert)->annex_a_amount
                    ?? $lines->sum(function($l) {
                        return (float)$l->transportation_cost + (float)$l->per_diem_amount;
                    }));

    $certDate = optional($cert)->certified_at
                  ? $cert->certified_at->format('F d, Y')
                  : now()->format('F d, Y');

    $notedByName    = optional($oo)->approving_officer_name    ?? '';
    $notedByPos     = optional($oo)->approving_officer_position ?? '';
    $officeDivision = optional(optional($emp)->division)->name  ?? '';
@endphp

{{-- ── ANNEX A LABEL ── --}}
<div class="annex-label">Annex A</div>

{{-- ── HEADER ── --}}
<div class="header-wrap">
    <div class="h-republic">Republic of the Philippines</div>
    <div class="h-agency">DEPARTMENT OF LABOR AND EMPLOYMENT</div>
    <div class="h-ro">Regional Office No. 9</div>
    <div class="h-address">Cortez Building, Dr. Evangelista Street</div>
    <div class="h-address">Sta. Catalina, Zamboanga City</div>
</div>

<div class="doc-title">Certification of Expenses Not Requiring Receipts</div>
<div class="doc-subtitle">Pursuant to COA Circular No. 2017-001 dated June 19, 2017</div>

{{-- ── EMPLOYEE INFO ── --}}
<table class="emp-tbl">
    <tr>
        <td class="e-lbl">Name of Employee:</td>
        <td class="e-val" style="width:50%;">{{ $empName }}</td>
        <td class="e-lbl" style="width:115px;">Employee No.</td>
        <td class="e-val">{{ optional($emp)->employee_no ?? '' }}</td>
    </tr>
    <tr>
        <td class="e-lbl">Office</td>
        <td colspan="3" class="e-val">{{ optional($emp)->office ?? 'DOLE REGION 9' }}</td>
    </tr>
    <tr>
        <td class="e-lbl">Division</td>
        <td colspan="3" class="e-val">{{ strtoupper($officeDivision) ?: 'TECHNICAL SUPPORT AND SERVICES DIVISION' }}</td>
    </tr>
</table>

{{-- ── EXPENSES TABLE ── --}}
<table class="exp-tbl">
    <thead>
        <tr>
            <th style="width:105px;">Date</th>
            <th style="width:120px;">Means of<br>Transportation</th>
            <th>Particulars</th>
            <th style="width:95px;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($expRows as $line)
        @php
            $amount      = (float)$line->transportation_cost + (float)$line->per_diem_amount;
            $particulars = strtoupper($line->origin) . ' TO ' . strtoupper($line->destination);
        @endphp
        <tr>
            <td>{{ $line->travel_date->format('d F, Y') }}</td>
            <td>{{ strtoupper($line->mode_of_transport ?? '—') }}</td>
            <td class="td-l">{{ $particulars }}</td>
            <td class="td-r">{{ number_format($amount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align:center; padding:10px; font-style:italic; color:#666;">
                No expense entries.
            </td>
        </tr>
        @endforelse

        @for ($i = 0; $i < max(0, 3 - $expRows->count()); $i++)
        <tr><td style="height:18px;">&nbsp;</td><td></td><td></td><td></td></tr>
        @endfor

        <tr class="row-total">
            <td colspan="3" style="text-align:center; letter-spacing:0.05em;">TOTAL</td>
            <td class="td-r">{{ number_format($annexATotal, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- ── PURPOSE ── --}}
<table class="purpose-tbl">
    <tr>
        <td style="width:70px;">Purpose:</td>
        <td>{{ $tev->purpose }}{{ $tev->destination ? ' ' . $tev->destination : '' }}</td>
    </tr>
</table>

{{-- ── CERTIFICATION STATEMENT ── --}}
<div class="cert-italic">
    I hereby certify that the above expenses are incurred as they are necessary for the above cited purpose,
    that above goods and services from parties not issuing receipts. And I am fully aware that willful
    falsification of statements is punishable by law.
</div>

{{-- ── SIGNATURE BLOCK ── --}}
<table class="sig-tbl">
    <tr>
        <td style="width:50%;"><span class="sig-section-lbl">Certified Correct:</span></td>
        <td style="width:50%;"><span class="sig-section-lbl">Noted by:</span></td>
    </tr>
    <tr>
        <td style="padding-top:5px; height:32px;">
            <span style="font-size:7.5pt; color:#777;">Signature</span>
        </td>
        <td style="padding-top:5px;">
            <span style="font-size:7.5pt; color:#777;">Signature</span>
        </td>
    </tr>
    <tr>
        <td>
            <span style="font-size:7.5pt; color:#777;">Printed Name &nbsp;</span>
            <span class="sig-name-bold">{{ $empName }}</span>
        </td>
        <td>
            <span style="font-size:7.5pt; color:#777;">Printed Name &nbsp;</span>
            <span class="sig-name-bold">{{ strtoupper($notedByName) }}</span>
        </td>
    </tr>
    <tr>
        <td><span class="sig-role">Employee</span></td>
        <td><span class="sig-role">{{ $notedByPos }}</span></td>
    </tr>
    <tr>
        <td style="text-align:center;">
            <span style="font-size:8.5pt;">{{ $certDate }}</span>
        </td>
        <td></td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse;">
    <tr><td style="border:1px solid #000; border-top:none; height:14px;"></td></tr>
    <tr><td style="border:1px solid #000; border-top:none; height:14px;"></td></tr>
</table>

{{-- ── FOOTER ── --}}
<div class="doc-footer">
    <span>{{ $tev->tev_no }} &nbsp;|&nbsp; COA Circular No. 2017-001</span>
    <span>Generated: {{ now()->format('F j, Y \a\t h:i A') }}</span>
</div>

</div>{{-- end .sheet --}}
</div>{{-- end .page-canvas --}}
</body>
</html>
{{-- resources/views/reports/tev-annex-a.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Annex A – {{ $tev->tev_no }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 20mm 20mm 12mm 20mm;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, sans-serif;
        font-size: 9pt;
        color: #000;
        background: #fff;
    }

    .page {
        width: 100%;
        max-width: 180mm;
        margin: 0 auto;
    }

    /* ── ANNEX A label top-right ── */
    .annex-label {
        text-align: right;
        font-style: italic;
        font-size: 9.5pt;
        font-weight: bold;
        margin-bottom: 3px;
        color: #000;
    }

    /* ── HEADER ── */
    .header-wrap {
        text-align: center;
        margin-bottom: 5px;
        padding-bottom: 4px;
        border-bottom: 2px solid #1a1a8c;
    }
    .h-republic  { font-size: 8pt; }
    .h-agency    { font-size: 13pt; font-weight: bold; letter-spacing: 0.01em; }
    .h-ro        { font-size: 9pt; }
    .h-address   { font-size: 8pt; }

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
    .e-lbl { width: 125px; }
    .e-val { font-weight: bold; }

    /* ── EXPENSES TABLE ── */
    .exp-tbl {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
        font-size: 9pt;
    }
    .exp-tbl th {
        border: 1px solid #000;
        padding: 5px 5px;
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

    .row-total td {
        font-weight: bold;
        font-size: 9.5pt;
        background-color: #f0f0f0;
    }

    /* ── PURPOSE ROW ── */
    .purpose-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .purpose-tbl td {
        border: 1px solid #000;
        padding: 5px 8px;
        vertical-align: top;
    }

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
    .sig-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .sig-tbl td {
        border: 1px solid #000;
        padding: 4px 8px;
        vertical-align: middle;
    }
    .sig-section-lbl {
        font-weight: bold;
        font-size: 8.5pt;
    }
    .sig-name-bold {
        font-weight: bold;
        font-size: 10pt;
        text-transform: uppercase;
    }
    .sig-role  { font-size: 8pt; color: #333; }
    .sig-date  { font-size: 8.5pt; }

    /* ── FOOTER ── */
    .footer {
        margin-top: 8px;
        font-size: 6.5pt;
        color: #666;
        border-top: 1px solid #ccc;
        padding-top: 3px;
        display: flex;
        justify-content: space-between;
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

    $empName = $emp
        ? strtoupper(trim(
            $emp->last_name . ', ' . $emp->first_name . ' ' .
            ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')
          ))
        : '—';

    $expRows = $lines->filter(fn($l) =>
        (float)$l->transportation_cost > 0 || (float)$l->per_diem_amount > 0
    );

    $annexATotal = (float)(optional($cert)->annex_a_amount
                    ?? $lines->sum(fn($l) => (float)$l->transportation_cost + (float)$l->per_diem_amount));

    $certDate = optional($cert)->certified_at
                  ? $cert->certified_at->format('F d, Y')
                  : now()->format('F d, Y');

    $notedByName = optional($oo)->approving_officer_name    ?? '';
    $notedByPos  = optional($oo)->approving_officer_position ?? '';
    $officeDivision = optional(optional($emp)->division)->name ?? '';
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
        <td class="e-lbl" style="width:130px;">Name of Employee:</td>
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
            <th style="width:100px;">Date</th>
            <th style="width:115px;">Means of<br>Transportation</th>
            <th>Particulars</th>
            <th style="width:90px;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($expRows as $line)
            @php
                $amount = (float)$line->transportation_cost + (float)$line->per_diem_amount;
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

        {{-- buffer rows --}}
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
        <td style="width:50%;">
            <span class="sig-section-lbl">Certified Correct:</span>
        </td>
        <td style="width:50%;">
            <span class="sig-section-lbl">Noted by:</span>
        </td>
    </tr>
    <tr>
        <td style="padding-top:5px; height:30px;">
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
        <td>
            <span class="sig-role">Employee</span>
        </td>
        <td>
            <span class="sig-role">{{ $notedByPos }}</span>
        </td>
    </tr>
    <tr>
        <td style="text-align:center;">
            <span class="sig-date">{{ $certDate }}</span>
        </td>
        <td></td>
    </tr>
</table>

{{-- bottom spacing rows --}}
<table style="width:100%; border-collapse:collapse;">
    <tr><td style="border:1px solid #000; border-top:none; height:14px;"></td></tr>
    <tr><td style="border:1px solid #000; border-top:none; height:14px;"></td></tr>
</table>

{{-- ── FOOTER ── --}}
<div class="footer">
    <span>{{ $tev->tev_no }} &nbsp;|&nbsp; COA Circular No. 2017-001</span>
    <span>Generated: {{ now()->format('F j, Y \a\t h:i A') }}</span>
</div>

</div>{{-- end .page --}}
</body>
</html>
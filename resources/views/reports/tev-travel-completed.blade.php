{{-- resources/views/reports/tev-travel-completed.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certification of Travel Completed – {{ $tev->tev_no }}</title>
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

    /* ── ADDRESS HEADER ── */
    .addr-header {
        text-align: center;
        font-size: 8.5pt;
        line-height: 1.5;
    }

    /* ── TITLE ── */
    .doc-title {
        text-align: center;
        font-size: 12pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin: 10px 0 8px;
        padding-bottom: 5px;
        border-bottom: 2px solid #000;
    }

    /* ── MAIN FORM TABLE ── */
    .outer-tbl {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
    }
    .outer-tbl td {
        border: 1px solid #000;
        padding: 4px 8px;
        vertical-align: middle;
    }

    /* entity label/value */
    .e-lbl { font-size: 8.5pt; }
    .e-val  { font-weight: bold; font-size: 9.5pt; }

    /* officer name */
    .officer-name { font-weight: bold; font-size: 10.5pt; }
    .officer-pos  { font-size: 8pt; color: #333; }

    /* body paragraph */
    .body-cell {
        font-size: 9pt;
        line-height: 1.75;
        padding: 9px 11px;
        text-align: justify;
    }
    .underline-field {
        display: inline-block;
        border-bottom: 1px solid #000;
        min-width: 120px;
    }

    /* checkbox rows */
    .cb-wrap {
        padding: 5px 8px;
    }
    .cb-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 5px;
        gap: 10px;
    }
    .checkbox {
        min-width: 15px;
        height: 15px;
        border: 1px solid #000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9pt;
        font-weight: bold;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .cb-text {
        font-size: 9pt;
        line-height: 1.5;
    }
    .sub-row {
        display: flex;
        align-items: baseline;
        padding-left: 25px;
        margin-bottom: 5px;
        font-size: 9pt;
        line-height: 1.5;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* explanation lines */
    .expl-cell {
        padding: 5px 8px 2px;
        font-size: 8.5pt;
    }
    .expl-line-cell {
        height: 22px;
        padding: 2px 8px;
        border-bottom: 1px solid #000;
    }

    /* evidence */
    .section-header {
        font-size: 8.5pt;
        padding: 6px 8px 2px;
    }

    /* signature */
    .sig-label-sm { font-size: 8.5pt; padding: 4px 8px 0; }
    .sig-name-bold {
        font-weight: bold;
        font-size: 10.5pt;
        text-transform: uppercase;
    }
    .sig-role { font-size: 8pt; color: #333; }
    .on-evidence {
        font-size: 8.5pt;
        font-style: italic;
        padding: 7px 10px;
        background-color: #fafafa;
    }

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
    $emp  = $tev->employee;
    $oo   = $tev->officeOrder;
    $cert = $tev->certification;

    $empName = $emp
        ? strtoupper(trim(
            $emp->last_name . ', ' . $emp->first_name . ' ' .
            ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')
          ))
        : '—';

    $ooNo   = optional($oo)->office_order_no ?? '';
    $ooDate = optional($oo)->issued_date
                ? \Carbon\Carbon::parse($oo->issued_date)->format('F j, Y')
                : '';

    $approvingOfficer  = optional($oo)->approving_officer_name     ?? 'ALBERT E. GUTIB';
    $approvingPosition = optional($oo)->approving_officer_position ?? 'Regional Director';
    $officeDivision    = optional(optional($emp)->division)->name   ?? '';

    // Checkbox states
    $isCompleted = (bool) optional($cert)->travel_completed;
    $isCutShort  = false;
    $isExtended  = false;
    $isOtherDev  = false;
    $checkStrict = $isCompleted && !$isCutShort && !$isExtended && !$isOtherDev;

    $hasTicket  = false;
    $hasCertApp = (bool) optional($cert)->agency_visited;
    $hasOtherEv = optional($oo)->office_order_no ? true : false;

    $excessAmount  = '';
    $refundedUnder = '';
    $refundedDate  = '';

    // Noted by — from office order or employee supervisor
    $notedByName = optional($oo)->noted_by_name ?? '';
    $notedByPos  = optional($oo)->noted_by_position ?? '';
@endphp

{{-- ── ADDRESS HEADER ── --}}
<div class="addr-header">Cortez Building, Dr. Evangelista Street</div>
<div class="addr-header">Sta. Catalina, Zamboanga City</div>

{{-- ── TITLE ── --}}
<div class="doc-title">Certification of Travel Completed</div>

{{-- ── MAIN FORM TABLE ── --}}
<table class="outer-tbl">

    {{-- Entity Name + Fund Cluster --}}
    <tr>
        <td style="width:105px;" class="e-lbl">Entity Name:</td>
        <td style="width:40%;" class="e-val">DOLE RO IX</td>
        <td style="width:90px;" class="e-lbl">Fund Cluster:</td>
        <td class="e-val">01101101</td>
    </tr>

    {{-- Approving Officer / Division --}}
    <tr>
        <td colspan="2" style="text-align:center; padding:5px 8px;">
            <div class="officer-name">{{ $approvingOfficer }}</div>
            <div class="officer-pos">{{ $approvingPosition }}</div>
        </td>
        <td colspan="2" style="text-align:center; padding:5px 8px;">
            <div class="officer-name">{{ strtoupper($officeDivision) ?: 'TECHNICAL SUPPORT &amp; SERVICES DIVISION' }}</div>
            <div class="officer-pos">Station</div>
        </td>
    </tr>

    {{-- Body paragraph --}}
    <tr>
        <td colspan="4" class="body-cell">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I&nbsp; HEREBY&nbsp; CERTIFY&nbsp; THAT&nbsp; I&nbsp; have&nbsp; completed&nbsp; the&nbsp; travel
            &nbsp;as&nbsp; authorized&nbsp; in&nbsp; the&nbsp; Travel&nbsp; Order/Itinerary&nbsp; of
            Travel No.&nbsp;<span class="underline-field">{{ $ooNo }}</span>
            &nbsp;dated&nbsp;<span class="underline-field">{{ $ooDate }}</span>
            &nbsp;under&nbsp; conditions&nbsp; indicated&nbsp; below:
        </td>
    </tr>

    {{-- Checkbox rows: travel status --}}
    <tr>
        <td colspan="4" class="cb-wrap">
            {{-- Strictly in accordance --}}
            <div class="cb-row">
                <div class="checkbox">{{ $checkStrict ? 'x' : '' }}</div>
                <span class="cb-text">Strictly in accordance with the approved itinerary.</span>
            </div>

            {{-- Cut short --}}
            <div class="cb-row">
                <div class="checkbox">{{ $isCutShort ? 'x' : '' }}</div>
                <span class="cb-text">
                    Cut short as explained below.&nbsp; Excess payment in the amount of&nbsp;
                    <span class="underline-field" style="min-width:140px;">{{ $excessAmount }}</span>
                </span>
            </div>

            {{-- Refunded under (sub-row) --}}
            <div class="sub-row">
                was refunded under&nbsp;
                <span class="underline-field" style="min-width:200px;">{{ $refundedUnder }}</span>
                &nbsp;dated&nbsp;
                <span class="underline-field" style="min-width:90px;">{{ $refundedDate }}</span>.
            </div>

            {{-- Extended --}}
            <div class="cb-row">
                <div class="checkbox">{{ $isExtended ? 'x' : '' }}</div>
                <span class="cb-text">Extended as explained below, additional itinerary was submitted</span>
            </div>

            {{-- Other deviations --}}
            <div class="cb-row">
                <div class="checkbox">{{ $isOtherDev ? 'x' : '' }}</div>
                <span class="cb-text">Other deviations as explained below.</span>
            </div>
        </td>
    </tr>

    {{-- Explanation / justification --}}
    <tr>
        <td colspan="4" class="expl-cell">Explanation or justifications:</td>
    </tr>
    <tr>
        <td colspan="4" class="expl-line-cell">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4" style="height:22px; padding: 2px 8px;">&nbsp;</td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="4" style="height:6px; border:none;"></td></tr>

    {{-- Evidence of travel --}}
    <tr>
        <td colspan="4" class="section-header">Evidence of travel:</td>
    </tr>
    <tr>
        <td colspan="4" class="cb-wrap" style="padding-top:4px;">
            <div class="cb-row">
                <div class="checkbox">{{ $hasTicket ? 'x' : '' }}</div>
                <span class="cb-text">Used Ticket</span>
            </div>
            <div class="cb-row">
                <div class="checkbox">{{ $hasCertApp ? 'x' : '' }}</div>
                <span class="cb-text">Certificate of Appearance</span>
            </div>
            <div class="cb-row">
                <div class="checkbox">{{ $hasOtherEv ? 'x' : '' }}</div>
                <span class="cb-text">
                    Others:&nbsp;
                    <span class="underline-field" style="min-width:200px;">
                        {{ $hasOtherEv ? 'OFFICE ORDER' : '' }}
                    </span>
                </span>
            </div>
            <div style="height:18px; border-bottom:1px solid #000; margin: 4px 0 2px 25px;">&nbsp;</div>
        </td>
    </tr>

    {{-- Respectfully submitted --}}
    <tr>
        <td colspan="2" style="border:none; padding:4px 8px;"></td>
        <td colspan="2" class="sig-label-sm">Respectfully submitted:</td>
    </tr>
    <tr>
        <td colspan="2" style="border:none; padding:4px 8px;"></td>
        <td colspan="2" style="padding: 4px 8px; text-align:center;">
            <div class="sig-name-bold">{{ $empName }}</div>
            <div class="sig-role">Employee</div>
        </td>
    </tr>

    {{-- On evidence --}}
    <tr>
        <td colspan="4" class="on-evidence">
            On evidence and information of which I have the knowledge, the travel was actually undertaken.
        </td>
    </tr>

    {{-- Approved by --}}
    <tr>
        <td colspan="2" style="border:none; padding:4px 8px;"></td>
        <td colspan="2" class="sig-label-sm">Approved:</td>
    </tr>
    <tr>
        <td colspan="2" style="border:none; padding:4px 8px;"></td>
        <td colspan="2" style="padding: 4px 8px; text-align:center;">
            @if ($notedByName)
                <div class="sig-name-bold">{{ strtoupper($notedByName) }}</div>
                <div class="sig-role">{{ $notedByPos }}</div>
            @else
                <div style="height:20px;">&nbsp;</div>
                <div class="sig-role">&nbsp;</div>
            @endif
        </td>
    </tr>

    {{-- Blank spacing rows --}}
    <tr><td colspan="4" style="height:10px;"></td></tr>
    <tr><td colspan="4" style="height:10px;"></td></tr>

</table>

{{-- ── FOOTER ── --}}
<div class="footer">
    <span>{{ $tev->tev_no }}</span>
    <span>Generated: {{ now()->format('F j, Y \a\t h:i A') }}</span>
</div>

</div>{{-- end .page --}}
</body>
</html>
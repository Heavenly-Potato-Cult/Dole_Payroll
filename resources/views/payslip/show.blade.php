<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Payslip — {{ $employee->full_name }} — {{ $periodLabel }}</title>
<style>
/* ================================================================
   DOLE RO9 Payslip — DomPDF Stylesheet
   A4 Portrait | Two-column layout (1st cut-off | 2nd cut-off)
   Mimics the official Excel payslip template exactly.
================================================================ */

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 7.5pt;
    color: #000;
    background: #fff;
    line-height: 1.3;
}

/* ── Page wrapper: two payslip columns side-by-side ── */
.page {
    width: 100%;
    padding: 6mm 5mm;
}

.two-col {
    width: 100%;
    border-collapse: collapse;
}

.two-col > tbody > tr > td {
    width: 50%;
    vertical-align: top;
    padding: 0;
}

/* Divider between the two columns */
.col-divider {
    width: 4mm;
    border-left: 1px dashed #aaa;
}

/* ── Single payslip card ── */
.slip {
    width: 100%;
    border: 1px solid #222;
    font-size: 7.2pt;
}

/* ── Header block ── */
.slip-header {
    border-bottom: 1px solid #222;
    padding: 3px 5px 2px;
    text-align: center;
}

.slip-header .republic {
    font-size: 6.5pt;
    font-style: italic;
    color: #333;
}

.slip-header .agency {
    font-size: 8pt;
    font-weight: bold;
    letter-spacing: 0.01em;
}

.slip-header .ro {
    font-size: 7pt;
    color: #222;
}

.slip-header .payslip-for {
    margin-top: 2px;
    font-size: 7.5pt;
    font-weight: bold;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.slip-header .period-label {
    font-size: 7pt;
    color: #333;
}

/* ── Employee info strip ── */
.slip-employee {
    border-bottom: 1px solid #bbb;
    padding: 3px 5px;
}

.slip-employee table {
    width: 100%;
    border-collapse: collapse;
}

.slip-employee td {
    font-size: 7pt;
    padding: 1px 2px;
    vertical-align: top;
}

.slip-employee .label {
    color: #555;
    font-size: 6.5pt;
    white-space: nowrap;
    width: 60px;
}

.slip-employee .value {
    font-weight: bold;
    color: #111;
}

/* ── Body rows table ── */
.slip-rows {
    width: 100%;
    border-collapse: collapse;
}

.slip-rows tr td {
    padding: 1.4px 4px;
    font-size: 7pt;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
}

/* Label column */
.slip-rows .col-label {
    width: 55%;
    color: #222;
}

/* Amount columns */
.slip-rows .col-1st,
.slip-rows .col-2nd {
    width: 22.5%;
    text-align: right;
    font-size: 6.9pt;
    white-space: nowrap;
}

/* ── Row type styles ── */
.row-income .col-label {
    font-weight: bold;
    font-size: 7.2pt;
    color: #111;
    letter-spacing: 0.02em;
}

.row-income .col-1st,
.row-income .col-2nd {
    font-weight: bold;
    color: #111;
}

.row-spacer td {
    background: #1A2B6B;
    color: #fff;
    font-weight: bold;
    font-size: 6.8pt;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 2px 4px;
    border-bottom: none;
}

.row-deduction .col-label {
    color: #222;
}

.row-sub .col-label {
    padding-left: 10px;
    color: #444;
    font-style: italic;
}

.row-divider td {
    background: #f0f0f0;
    font-weight: bold;
    font-size: 7pt;
    border-top: 1.5px solid #888;
    border-bottom: 1.5px solid #888;
}

.row-net td {
    font-weight: bold;
    font-size: 7.8pt;
    border-bottom: 1px solid #444;
}

.row-net .col-label {
    background: #1A2B6B;
    color: #F9A825;
    padding: 2.5px 4px;
    letter-spacing: 0.04em;
    font-size: 7.2pt;
}

.row-net .col-1st,
.row-net .col-2nd {
    background: #FFF8E1;
    color: #7A5900;
    font-size: 8pt;
    font-weight: bold;
}

/* Highlight the current cut-off's column */
.col-1st-active {
    background: #E8F5E9 !important;
    color: #1B5E20 !important;
}

.col-2nd-active {
    background: #E8F5E9 !important;
    color: #1B5E20 !important;
}

/* Zero/blank amounts */
.amount-zero {
    color: #ccc;
}

/* ── Column sub-headers (1-15 / 16-30/31) ── */
.slip-col-headers {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1.5px solid #222;
}

.slip-col-headers td {
    padding: 2px 4px;
    font-size: 6.5pt;
    font-weight: bold;
    text-align: right;
    color: #333;
}

.slip-col-headers .col-label { text-align: left; width: 55%; }
.slip-col-headers .col-1st   { width: 22.5%; }
.slip-col-headers .col-2nd   { width: 22.5%; }

/* ── Signature block ── */
.slip-footer {
    border-top: 1.5px solid #222;
    padding: 4px 5px 3px;
}

.slip-footer .signatory {
    font-weight: bold;
    font-size: 7.5pt;
    color: #000;
}

.slip-footer .signatory-title {
    font-size: 6.5pt;
    color: #333;
}

.slip-footer .doc-ref {
    margin-top: 3px;
    font-size: 6pt;
    color: #555;
}

/* ── Tardiness/LWOP attendance note ── */
.row-attendance td {
    background: #FFF3E0;
    font-size: 6.5pt;
    color: #5D4037;
    padding: 1.5px 4px;
    border-bottom: 1px solid #FFE0B2;
}

</style>
</head>
<body>
<div class="page">
<table class="two-col">
<tbody>
<tr>

{{-- ════════════════════════════════════════════════════════
     LEFT COLUMN  (1st cut-off  1–15)
     RIGHT COLUMN (2nd cut-off 16–30/31)
     Both share the same employee; each shows its own amounts.
════════════════════════════════════════════════════════ --}}

@php
    $months = ['','January','February','March','April','May','June',
               'July','August','September','October','November','December'];
    $monthName = $months[$batch->period_month] ?? '';

    // Helper: get deduction amount for a cut-off by code
    $amt = function($dedMap, $code) {
        if (!$dedMap || !$code) return null;
        $d = $dedMap->get($code);
        return $d ? (float) $d->amount : null;
    };

    // Format amount: show '—' for null/zero, else formatted
    $fmt = function($val, $forceShow = false) {
        if ($val === null || (!$forceShow && $val == 0)) return null;
        return number_format($val, 2);
    };

    $cutoffIs1st = $batch->cutoff === '1st';
@endphp

@for ($col = 1; $col <= 2; $col++)
@php
    // Each loop renders one full payslip column
    $isLeft = ($col === 1);
    // In left column we show the 1st cut-off on left, 2nd on right
    // Both columns show the same two-sub-column layout (1-15 | 16-30/31)
@endphp

<td>
<table class="slip">

    {{-- ── Header ── --}}
    <tr><td>
    <div class="slip-header">
        <div class="republic">Republic of the Philippines</div>
        <div class="agency">DEPARTMENT OF LABOR AND EMPLOYMENT</div>
        <div class="ro">Regional Office No. 9</div>
        <div class="payslip-for">PAYSLIP FOR</div>
        <div class="period-label">{{ $monthName }} 1–31, {{ $batch->period_year }}</div>
    </div>
    </td></tr>

    {{-- ── Employee info ── --}}
    <tr><td>
    <div class="slip-employee">
        <table>
            <tr>
                <td class="label">Name:</td>
                <td class="value">{{ $employee->full_name }}</td>
            </tr>
            <tr>
                <td class="label">Position:</td>
                <td class="value">{{ $employee->position_title }}</td>
            </tr>
            <tr>
                <td class="label">Plantilla:</td>
                <td>{{ $employee->plantilla_item_no ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Division:</td>
                <td>{{ $employee->division->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">SG – Step:</td>
                <td>SG {{ $employee->salary_grade }} – Step {{ $employee->step }}</td>
            </tr>
        </table>
    </div>
    </td></tr>

    {{-- ── Cut-off sub-column headers ── --}}
    <tr><td>
    <table class="slip-col-headers">
        <tr>
            <td class="col-label"></td>
            <td class="col-1st">1–15</td>
            <td class="col-2nd">16–30/31</td>
        </tr>
    </table>
    </td></tr>

    {{-- ── Payslip rows ── --}}
    <tr><td>
    <table class="slip-rows">

    @foreach ($rows as $row)
    @php
        $type  = $row['type'];
        $label = $row['label'];
        $code  = $row['code'];

        // Amounts for each cut-off
        $a1 = null; // 1st cut-off amount
        $a2 = null; // 2nd cut-off amount

        switch ($type) {
            case 'income':
                if ($label === 'BASIC') {
                    $a1 = $entry1st ? (float) $entry1st->basic_salary : null;
                    $a2 = $entry2nd ? (float) $entry2nd->basic_salary : null;
                } elseif ($label === 'ALLOWANCE') {
                    $a1 = $entry1st ? (float) $entry1st->pera : null;
                    $a2 = $entry2nd ? (float) $entry2nd->pera : null;
                }
                break;

            case 'deduction':
            case 'sub':
                $a1 = $amt($ded1st, $code);
                $a2 = $amt($ded2nd, $code);
                break;

            case 'divider':
                // TOTAL = total_deductions for each cut-off
                $a1 = $entry1st ? (float) $entry1st->total_deductions : null;
                $a2 = $entry2nd ? (float) $entry2nd->total_deductions : null;
                break;

            case 'net':
                if ($label === 'NET PAY 1-15') {
                    $a1 = $entry1st ? (float) $entry1st->net_amount : null;
                    $a2 = null; // Only shows in 1-15 column
                } else {
                    $a1 = null;
                    $a2 = $entry2nd ? (float) $entry2nd->net_amount : null;
                }
                break;
        }

        $rowClass = match ($type) {
            'income'    => 'row-income',
            'spacer'    => 'row-spacer',
            'sub'       => 'row-sub',
            'divider'   => 'row-divider',
            'net'       => 'row-net',
            default     => 'row-deduction',
        };

        // Highlight the active (current) cut-off column
        $active1 = $cutoffIs1st ? 'col-1st-active' : '';
        $active2 = !$cutoffIs1st ? 'col-2nd-active' : '';
    @endphp

    @if ($type === 'spacer')
    <tr class="{{ $rowClass }}">
        <td colspan="3">{{ $label }}</td>
    </tr>

    @elseif ($type === 'net')
    <tr class="{{ $rowClass }}">
        <td class="col-label">{{ $label }}</td>
        <td class="col-1st {{ $active1 }}">
            @if ($a1 !== null)
                {{ $fmt($a1, true) }}
            @endif
        </td>
        <td class="col-2nd {{ $active2 }}">
            @if ($a2 !== null)
                {{ $fmt($a2, true) }}
            @endif
        </td>
    </tr>

    @else
    <tr class="{{ $rowClass }}">
        <td class="col-label">{{ $label }}</td>
        <td class="col-1st {{ $type === 'net' ? $active1 : '' }}">
            @if ($a1 !== null && $a1 != 0)
                {{ $fmt($a1) }}
            @elseif ($type === 'income')
                <span class="amount-zero">—</span>
            @endif
        </td>
        <td class="col-2nd {{ $type === 'net' ? $active2 : '' }}">
            @if ($a2 !== null && $a2 != 0)
                {{ $fmt($a2) }}
            @elseif ($type === 'income')
                <span class="amount-zero">—</span>
            @endif
        </td>
    </tr>
    @endif

    @endforeach

    {{-- ── Attendance deductions row (if any) ── --}}
    @php
        $tardy1 = $entry1st ? round(($entry1st->tardiness ?? 0) + ($entry1st->undertime ?? 0), 2) : 0;
        $lwop1  = $entry1st ? round($entry1st->lwop_deduction ?? 0, 2) : 0;
        $tardy2 = $entry2nd ? round(($entry2nd->tardiness ?? 0) + ($entry2nd->undertime ?? 0), 2) : 0;
        $lwop2  = $entry2nd ? round($entry2nd->lwop_deduction ?? 0, 2) : 0;
        $showAttendance = ($tardy1 + $lwop1 + $tardy2 + $lwop2) > 0;
    @endphp

    @if ($showAttendance)
    <tr class="row-attendance">
        <td class="col-label">Tardiness / LWOP</td>
        <td class="col-1st">
            @if (($tardy1 + $lwop1) > 0)
                {{ number_format($tardy1 + $lwop1, 2) }}
            @endif
        </td>
        <td class="col-2nd">
            @if (($tardy2 + $lwop2) > 0)
                {{ number_format($tardy2 + $lwop2, 2) }}
            @endif
        </td>
    </tr>
    @endif

    </table>
    </td></tr>

    {{-- ── Signatory footer ── --}}
    <tr><td>
    <div class="slip-footer">
        <div class="signatory">AIRA D. LAGRADILLA</div>
        <div class="signatory-title">Labor Employment Officer III, HRMO Designate</div>
        <div class="doc-ref">
            D9FI-550308 Rev. 01 &nbsp;·&nbsp;
            Email: ro9@dole.gov.ph &nbsp;·&nbsp;
            Tel: (062) 991-2673 · (062) 991-3376 &nbsp;·&nbsp;
            Website: ro9.dole.gov.ph
        </div>
    </div>
    </td></tr>

</table>
</td>

@if ($col === 1)
<td class="col-divider">&nbsp;</td>
@endif

@endfor

</tr>
</tbody>
</table>
</div>
</body>
</html>
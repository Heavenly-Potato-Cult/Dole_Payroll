<?php

namespace App\Exports;

use App\Models\PayrollDeduction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GsisSummaryExport implements FromCollection, WithTitle, WithEvents
{
    /**
     * Rows definition for the GSIS Summary Remittance sheet.
     * Format: [section_label|null, account_label|null, deduction_code|null]
     *
     * Codes match DeductionTypeSeeder exactly.
     * GS (Govt Share) and EC are employer-side costs — they do NOT appear
     * in payroll_deductions; those rows are intentionally left blank.
     *
     * Row order matches the official GSIS Summary template (02-GSIS-Remittance.xls).
     */
    private const ROWS = [
        ['PREMIUMS', null,                                             null],
        [null, 'Life/Retirement Premium Personal Share',               'GSIS_LIFE_RETIREMENT'],
        [null, 'Life/Retirement Premium Govt. Share',                  null],
        [null, 'Employees Compensation Premium (EC)',                  null],
        ['OPTIONAL', null,                                             null],
        [null, 'Unlimited Optional Life Insurance',                    null],
        ['PRENEED', null,                                              null],
        [null, 'HIP Premium Income',                                   null],
        [null, 'CEAP Premium Income',                                  null],
        [null, 'Edu-Child Plan Income',                                null],
        [null, 'Genesis PLUS (Mem Serv Ins Income)',                   null],
        [null, 'Genesis FLEXI (Mem Serv Ins Income)',                  null],
        [null, 'Genesis (Mem Serv Ins Income)',                        null],
        [null, 'Genesis Special (Mem Serv Ins Income)',                null],
        [null, 'Family Hospitalization Plan - CIGNA',                  null],
        ['LOANS', null,                                                null],
        [null, 'Salary Loan',                                          null],
        [null, 'Emergency Loan',                                       'GSIS_EMERGENCY'],
        [null, 'Educational Assistance Loan',                          'GSIS_EDUC'],
        [null, 'Policy Loan - regular',                                null],
        [null, 'Loan Restructuring Program',                           null],
        [null, 'Retirement Plan',                                      null],
        [null, 'Emergency Loan Assistance (ELA)',                      null],
        [null, 'Summer One Month Salary Loan (SOS)',                   null],
        [null, 'Fly Now Pay Later Loan',                               null],
        [null, 'Multi-Purpose Loan Lite (MPL Lite)',                   'GSIS_MPL_LITE'],
        [null, 'Cash Advance Loan',                                    null],
        [null, 'Restructured Salary Loan',                             null],
        [null, 'Enhanced Salary Loan',                                 null],
        [null, 'Consolidated Loan',                                    'GSIS_CONSO'],
        [null, 'Ecard Plus (Cash Advance)',                            null],
        [null, 'Home Emergency Loan',                                  'GSIS_HELP'],
        [null, 'GSIS Financial Assistance Program (GFAL)',             'GSIS_GFAL'],
        [null, 'Multi-Purpose Loan (MPL)',                             'GSIS_MPL'],
        [null, 'GSIS Computer Loan (CPL)',                             'GSIS_CPL'],
        ['OPTIONAL', null,                                             null],
        [null, 'Policy Loan - optional',                               'GSIS_POLICY'],
        ['HOUSING', null,                                              null],
        [null, 'Real Estate Loan',                                     'GSIS_REAL_ESTATE'],
        [null, 'LCH',                                                  null],
    ];

    private int $year;
    private int $month;
    private string $cutoff;
    private string $dueMonth;

    /** code => summed amount for the period */
    private array $totals = [];
    private int $employeeCount = 0;

    public function __construct(int $year, int $month, string $cutoff = 'both')
    {
        $this->year     = $year;
        $this->month    = $month;
        $this->cutoff   = $cutoff;
        $this->dueMonth = sprintf('%02d/%04d', $month, $year);

        $this->compute();
    }

    private function compute(): void
    {
        $codes = array_values(array_filter(array_column(self::ROWS, 2)));

        $rows = PayrollDeduction::query()
            ->whereIn('code', $codes)
            ->whereHas('entry.batch', function ($q) {
                $q->whereYear('period_start', $this->year)
                  ->whereMonth('period_start', $this->month);
                if ($this->cutoff === '1st') {
                    $q->where('cutoff', '1st');
                } elseif ($this->cutoff === '2nd') {
                    $q->where('cutoff', '2nd');
                }
            })
            ->selectRaw('code, SUM(amount) as total')
            ->groupBy('code')
            ->get();

        foreach ($rows as $row) {
            $this->totals[$row->code] = (float) $row->total;
        }

        $this->employeeCount = PayrollDeduction::query()
            ->whereIn('code', $codes)
            ->whereHas('entry.batch', function ($q) {
                $q->whereYear('period_start', $this->year)
                  ->whereMonth('period_start', $this->month);
                if ($this->cutoff === '1st') {
                    $q->where('cutoff', '1st');
                } elseif ($this->cutoff === '2nd') {
                    $q->where('cutoff', '2nd');
                }
            })
            ->join('payroll_entries', 'payroll_deductions.payroll_entry_id', '=', 'payroll_entries.id')
            ->distinct('payroll_entries.employee_id')
            ->count('payroll_entries.employee_id');
    }

    public function collection(): Collection
    {
        return collect([]);
    }

    public function title(): string
    {
        return 'Summary GSIS Remittance';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->buildSheet($event->sheet->getDelegate());
            },
        ];
    }

    private function buildSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws): void
    {
        // ── Header block ──────────────────────────────────────────────────────
        $ws->setCellValue('A1', 'REMITTING AGENCY:');
        $ws->setCellValue('B1', 'DEPARTMENT OF LABOR & EMPLOYMENT, REGIONAL OFFICE IX');
        $ws->setCellValue('A2', 'DUE MONTH:');
        $ws->setCellValue('B2', $this->dueMonth);
        $ws->setCellValue('A3', 'OFFICE CODE:');
        $ws->setCellValue('B3', '1000032479');

        foreach (['A1', 'A2', 'A3'] as $cell) {
            $ws->getStyle($cell)->getFont()->setBold(true);
        }

        // ── Column heading row 5 ──────────────────────────────────────────────
        $ws->setCellValue('A5', 'TYPE');
        $ws->setCellValue('C5', 'ACCOUNT');
        $ws->setCellValue('E5', 'AMOUNT');
        $ws->getStyle('A5:E5')->getFont()->setBold(true);
        $ws->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ── Data rows starting at row 6 ───────────────────────────────────────
        $r = 6;
        foreach (self::ROWS as [$section, $account, $code]) {
            if ($section !== null) {
                $ws->setCellValue("A{$r}", $section);
                $ws->getStyle("A{$r}")->getFont()->setBold(true);
            }
            if ($account !== null) {
                $ws->setCellValue("C{$r}", $account);
                if ($code !== null) {
                    $ws->setCellValue("E{$r}", $this->totals[$code] ?? 0);
                    $ws->getStyle("E{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
                }
            }
            $r++;
        }

        // ── Grand Total ───────────────────────────────────────────────────────
        $r++;
        $ws->setCellValue("C{$r}", 'GRAND TOTAL');
        $ws->setCellValue("E{$r}", array_sum($this->totals));
        $ws->getStyle("C{$r}")->getFont()->setBold(true);
        $ws->getStyle("E{$r}")->getFont()->setBold(true);
        $ws->getStyle("E{$r}")->getNumberFormat()->setFormatCode('#,##0.00');

        // ── Employee count ────────────────────────────────────────────────────
        $r += 2;
        $ws->setCellValue("A{$r}", "EMPLOYEE COUNT: {$this->employeeCount}");
        $ws->getStyle("A{$r}")->getFont()->setBold(true);

        // ── Signature block ───────────────────────────────────────────────────
        $r += 2;
        $ws->setCellValue("A{$r}", 'Prepared by:');
        $ws->setCellValue("D{$r}", 'Certified by:');
        $r += 2;
        $ws->setCellValue("A{$r}", 'NAME');
        $ws->setCellValue("D{$r}", 'NAME');
        $r++;
        $ws->setCellValue("A{$r}", 'Position');
        $ws->setCellValue("D{$r}", 'Position');
        $r++;
        $ws->setCellValue("A{$r}", 'HRMO / HRMO Designate');
        $ws->setCellValue("D{$r}", 'Internal Management Service Division');

        // ── Column widths ─────────────────────────────────────────────────────
        $ws->getColumnDimension('A')->setWidth(14);
        $ws->getColumnDimension('B')->setWidth(50);
        $ws->getColumnDimension('C')->setWidth(48);
        $ws->getColumnDimension('D')->setWidth(14);
        $ws->getColumnDimension('E')->setWidth(18);
    }

    // ── Accessors used by ReportController preview ────────────────────────────

    public function getTotals(): array
    {
        return $this->totals;
    }

    public function getEmployeeCount(): int
    {
        return $this->employeeCount;
    }
}
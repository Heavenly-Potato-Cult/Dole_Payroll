<?php

namespace App\Exports;

use App\Models\PayrollBatch;
use App\Models\PayrollDeduction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * CARESS IX Mortuary Death Benefit Schedule
 *
 * Formula per row:
 *   Daily Rate  = Basic Monthly Salary / 22
 *   Col E (0.25) = Daily Rate × 25%
 *   Col F (0.25) = Daily Rate × 25%
 *   Col G (0.50) = Daily Rate × 50%
 *   Total       = E + F + G
 */
class CaressMortuaryExport implements FromCollection, WithTitle, WithColumnWidths, WithEvents
{
    protected int $year;
    protected int $month;
    protected string $cutoff;
    protected Collection $rows;

    protected float $totalE     = 0;
    protected float $totalF     = 0;
    protected float $totalG     = 0;
    protected float $grandTotal = 0;

    public function __construct(int $year, int $month, string $cutoff)
    {
        $this->year   = $year;
        $this->month  = $month;
        $this->cutoff = $cutoff;
        $this->rows   = $this->buildRows();
    }

    protected function buildRows(): Collection
    {
        $batches = PayrollBatch::query()
            ->whereYear('period_start', $this->year)
            ->whereMonth('period_start', $this->month)
            ->when($this->cutoff === '1st', fn($q) => $q->whereDay('period_start', '<=', 15))
            ->when($this->cutoff === '2nd', fn($q) => $q->whereDay('period_start', '>', 15))
            ->pluck('id');

        $deductionTypeId = \App\Models\DeductionType::where('code', 'CARESS_MORTUARY')->value('id');

        $rows = PayrollDeduction::with('payrollEntry.employee')
            ->whereIn('payroll_entry_id', function ($q) use ($batches) {
                $q->select('id')->from('payroll_entries')->whereIn('payroll_batch_id', $batches);
            })
            ->where('deduction_type_id', $deductionTypeId)
            ->where('amount', '>', 0)
            ->get()
            ->map(function ($ded) {
                $emp          = $ded->payrollEntry->employee;
                $basicMonthly = $emp->semi_monthly_gross * 2;
                $dailyRate    = round($basicMonthly / 22, 4);
                $e            = round($dailyRate * 0.25, 2);
                $f            = round($dailyRate * 0.25, 2);
                $g            = round($dailyRate * 0.50, 2);
                $total        = $e + $f + $g;

                $this->totalE     += $e;
                $this->totalF     += $f;
                $this->totalG     += $g;
                $this->grandTotal += $total;

                return [
                    'name'          => strtoupper($emp->last_name . ', ' . $emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')),
                    'basic_monthly' => $basicMonthly,
                    'daily_rate'    => $dailyRate,
                    'col_e'         => $e,
                    'col_f'         => $f,
                    'col_g'         => $g,
                    'total'         => $total,
                ];
            })
            ->sortBy('name')
            ->values();

        return $rows;
    }

    public function collection(): Collection
    {
        return collect([]);
    }

    public function title(): string
    {
        return 'CARESS Mortuary';
    }

    public function columnWidths(): array
    {
        // A=NO, B=NAME, C=BASIC MONTHLY, D=DAILY RATE, E=0.25, F=0.25, G=0.5, H=TOTAL
        return ['A' => 5.5, 'B' => 36.0, 'C' => 18.0, 'D' => 14.0, 'E' => 11.0, 'F' => 11.0, 'G' => 11.0, 'H' => 14.0];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $monthName = date('F', mktime(0, 0, 0, $this->month, 1));
                $count     = $this->rows->count();

                // ── Row heights ──────────────────────────────────────────
                $sheet->getRowDimension(1)->setRowHeight(30);
                foreach (range(2, 5) as $r) $sheet->getRowDimension($r)->setRowHeight(16);
                foreach (range(8, 13) as $r) $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->getRowDimension(15)->setRowHeight(42); // Header – tall for wrap
                $sheet->getRowDimension(16)->setRowHeight(16); // Formula sub-row 1
                $sheet->getRowDimension(17)->setRowHeight(16); // Formula sub-row 2

                // ── LOGOS ────────────────────────────────────────────────
                $logoLeft = new Drawing();
                $logoLeft->setName('Bagong Pilipinas')->setDescription('Bagong Pilipinas Logo')
                    ->setPath(public_path('assets/img/bagong_pilipinas_logo.png'))
                    ->setHeight(60)->setCoordinates('A1')->setOffsetX(2)->setOffsetY(2)
                    ->setWorksheet($sheet);

                $logoRight = new Drawing();
                $logoRight->setName('DOLE Logo')->setDescription('DOLE Logo')
                    ->setPath(public_path('assets/img/dole_logo.png'))
                    ->setHeight(60)->setCoordinates('H1')->setOffsetX(2)->setOffsetY(2)
                    ->setWorksheet($sheet);

                // ── Agency header (rows 1–5, merged A:H) ─────────────────
                $agencyHeaders = [
                    1 => ['Republic of the Philippines',                  false, 11],
                    2 => ['DEPARTMENT OF LABOR AND EMPLOYMENT',           true,  13],
                    3 => ['Regional Office No. IX',                       false, 11],
                    4 => ['Cortez Building, Dr. Evangelista Street',      false, 10],
                    5 => ['Barangay Sta. Catalina, Zamboanga City',       false, 10],
                ];
                foreach ($agencyHeaders as $r => [$text, $bold, $sz]) {
                    $sheet->mergeCells("A{$r}:H{$r}");
                    $sheet->setCellValue("A{$r}", $text);
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => $sz],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // ── Document title (rows 8–10) ────────────────────────────
                $sheet->mergeCells('A8:H8');
                $sheet->setCellValue('A8', 'DEATH BENEFIT FOR THE (RELATIONSHIP TO THE PERSONNEL) OF');
                $sheet->mergeCells('A9:H9');
                $sheet->setCellValue('A9', 'NAME OF PERSONNEL');
                $sheet->mergeCells('A10:H10');
                $sheet->setCellValue('A10', "For {$monthName} {$this->year} Payroll");
                foreach (['A8', 'A9', 'A10'] as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 12],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ── Payee block (rows 12–13) ──────────────────────────────
                $sheet->mergeCells('A12:H12');
                $sheet->setCellValue('A12', 'PAYEE:  DOLE-CARESS9');
                $sheet->mergeCells('A13:H13');
                $sheet->setCellValue('A13', 'ADDRESS: ZAMBOANGA CITY');
                foreach (['A12', 'A13'] as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // ── Formula sub-header rows (15–17) ───────────────────────
                // Row 15 = main column header
                $headerRow  = 15;
                $mainHeader = ['A' => 'NO.', 'B' => 'NAME', 'C' => 'BASIC MONTHLY SALARY', 'D' => "DAILY\nRATE", 'E' => '0.25', 'F' => '0.25', 'G' => '0.5', 'H' => 'TOTAL'];
                foreach ($mainHeader as $col => $label) {
                    $sheet->setCellValue("{$col}{$headerRow}", $label);
                }
                $sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);

                // Row 16 = formula reference row 1
                $sub1 = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D = C / 22', 'E' => 'E = D × 25%', 'F' => 'F = E', 'G' => 'G = E', 'H' => 'H = F + G'];
                foreach ($sub1 as $col => $label) $sheet->setCellValue("{$col}16", $label);
                $sheet->getStyle('A16:H16')->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 9, 'italic' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DEEAF1']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Row 17 = formula reference row 2
                $sub2 = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D = C / 22', 'E' => 'E = D × 25%', 'F' => 'E = D × 25%', 'G' => 'E = D × 50%', 'H' => 'E = D × 25%'];
                foreach ($sub2 as $col => $label) $sheet->setCellValue("{$col}17", $label);
                $sheet->getStyle('A17:H17')->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 9, 'italic' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DEEAF1']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // ── Data rows ─────────────────────────────────────────────
                $numFmt    = '#,##0.00';
                $dataStart = 18;
                foreach ($this->rows as $idx => $row) {
                    $r  = $dataStart + $idx;
                    $no = $idx + 1;
                    $sheet->getRowDimension($r)->setRowHeight(20);
                    $sheet->setCellValue("A{$r}", $no);
                    $sheet->setCellValue("B{$r}", $row['name']);
                    $sheet->setCellValue("C{$r}", $row['basic_monthly']);
                    $sheet->setCellValue("D{$r}", "=ROUND((C{$r}/22),2)");
                    $sheet->setCellValue("E{$r}", "=ROUND((D{$r}*0.25),2)");
                    $sheet->setCellValue("F{$r}", "=ROUND((D{$r}*0.25),2)");
                    $sheet->setCellValue("G{$r}", "=ROUND((D{$r}*0.50),2)");
                    $sheet->setCellValue("H{$r}", "=SUM(E{$r}:G{$r})");

                    foreach (['C', 'D', 'E', 'F', 'G', 'H'] as $col) {
                        $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                    $sheet->getStyle("A{$r}:H{$r}")->applyFromArray([
                        'font'    => ['name' => 'Arial', 'size' => 10],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    if ($no % 2 === 0) {
                        $sheet->getStyle("A{$r}:H{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                    }
                }

                // ── Grand Total ───────────────────────────────────────────
                $lastData = $dataStart + $count - 1;
                $totalRow = $dataStart + $count;
                $sheet->getRowDimension($totalRow)->setRowHeight(20);
                $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'GRAND TOTAL');
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$dataStart}:E{$lastData})");
                $sheet->setCellValue("F{$totalRow}", "=SUM(F{$dataStart}:F{$lastData})");
                $sheet->setCellValue("G{$totalRow}", "=SUM(G{$dataStart}:G{$lastData})");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H{$dataStart}:H{$lastData})");
                $sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                foreach (['E', 'F', 'G', 'H'] as $col) {
                    $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("{$col}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // ── Signature block ───────────────────────────────────────
                $sigRow  = $totalRow + 3;
                $nameRow = $sigRow + 4;
                $sheet->setCellValue("A{$sigRow}", 'Prepared by:');
                $sheet->setCellValue("E{$sigRow}", 'Certified:');
                $sheet->getStyle("A{$sigRow}:H{$sigRow}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getStyle("A{$nameRow}:C{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("E{$nameRow}:H{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue("A" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("A" . ($nameRow + 2), 'Payroll-in-Charge');
                $sheet->setCellValue("E" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("E" . ($nameRow + 2), 'Accountant');
                foreach (['A', 'E'] as $col) {
                    $sheet->getStyle("{$col}" . ($nameRow + 1))->getFont()->setName('Arial')->setSize(10)->setBold(true);
                    $sheet->getStyle("{$col}" . ($nameRow + 2))->getFont()->setName('Arial')->setSize(10);
                }

                // ── Page setup ───────────────────────────────────────────
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER)
                    ->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
            },
        ];
    }
}

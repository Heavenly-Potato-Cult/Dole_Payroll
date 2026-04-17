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

class CaressUnionDuesExport implements FromCollection, WithTitle, WithColumnWidths, WithEvents
{
    protected int $year;
    protected int $month;
    protected string $cutoff;
    protected Collection $rows;
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

        $deductionTypeId = \App\Models\DeductionType::where('code', 'CARESS_UNION')->value('id');

        $rows = PayrollDeduction::with('payrollEntry.employee')
            ->whereIn('payroll_entry_id', function ($q) use ($batches) {
                $q->select('id')->from('payroll_entries')->whereIn('payroll_batch_id', $batches);
            })
            ->where('deduction_type_id', $deductionTypeId)
            ->where('amount', '>', 0)
            ->get()
            ->map(function ($ded) {
                $emp = $ded->payrollEntry->employee;
                return [
                    'name'        => strtoupper($emp->last_name . ', ' . $emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')),
                    'designation' => $emp->position ?? '',
                    'amount'      => $ded->amount,
                ];
            })
            ->sortBy('name')
            ->values();

        $this->grandTotal = $rows->sum('amount');
        return $rows;
    }

    public function collection(): Collection
    {
        return collect([]);
    }

    public function title(): string
    {
        return 'CARESS IX Union Dues';
    }

    public function columnWidths(): array
    {
        // A=spacer, B=NO, C=NAME, D=DESIGNATION, E=AMOUNT, F=REMARKS
        return ['A' => 1.5, 'B' => 6.0, 'C' => 38.0, 'D' => 26.0, 'E' => 14.0, 'F' => 18.0];
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
                $sheet->getRowDimension(2)->setRowHeight(18);
                foreach (range(3, 7) as $r) $sheet->getRowDimension($r)->setRowHeight(15);
                $sheet->getRowDimension(7)->setRowHeight(18);
                $sheet->getRowDimension(8)->setRowHeight(18);
                $sheet->getRowDimension(9)->setRowHeight(18);
                $sheet->getRowDimension(11)->setRowHeight(18);
                $sheet->getRowDimension(12)->setRowHeight(22);

                // ── LOGO – left (Bagong Pilipinas) ────────────────────────
                $logoLeft = new Drawing();
                $logoLeft->setName('Bagong Pilipinas');
                $logoLeft->setDescription('Bagong Pilipinas Logo');
                $logoLeft->setPath(public_path('assets/img/bagong_pilipinas_logo.png'));
                $logoLeft->setHeight(60);
                $logoLeft->setCoordinates('B1');
                $logoLeft->setOffsetX(2);
                $logoLeft->setOffsetY(2);
                $logoLeft->setWorksheet($sheet);

                // ── LOGO – right (DOLE) ───────────────────────────────────
                $logoRight = new Drawing();
                $logoRight->setName('DOLE Logo');
                $logoRight->setDescription('DOLE Logo');
                $logoRight->setPath(public_path('assets/img/dole_logo.png'));
                $logoRight->setHeight(60);
                $logoRight->setCoordinates('F1');
                $logoRight->setOffsetX(2);
                $logoRight->setOffsetY(2);
                $logoRight->setWorksheet($sheet);

                // ── Agency header (rows 1-5, merged B:F) ─────────────────
                $headers = [
                    1 => ['Republic of the Philippines',                  false, 11],
                    2 => ['DEPARTMENT OF LABOR AND EMPLOYMENT',           true,  13],
                    3 => ['Regional Office No. IX',                       false, 11],
                    4 => ['Cortez Building, Dr. Evangelista Street',      false, 10],
                    5 => ['Barangay Sta. Catalina, Zamboanga City',       false, 10],
                ];
                foreach ($headers as $r => [$text, $bold, $sz]) {
                    $sheet->mergeCells("B{$r}:F{$r}");
                    $sheet->setCellValue("B{$r}", $text);
                    $sheet->getStyle("B{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => $sz],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // ── Document title (rows 7-8) ─────────────────────────────
                $sheet->mergeCells('B7:F7');
                $sheet->setCellValue('B7', 'CARESS 9 - UNION DUES');
                $sheet->getStyle('B7')->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->mergeCells('B8:F8');
                $sheet->setCellValue('B8', strtoupper($monthName) . ' ' . $this->year);
                $sheet->getStyle('B8')->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Payee block (rows 10-11) ──────────────────────────────
                $sheet->mergeCells('B10:F10');
                $sheet->setCellValue('B10', 'PAYEE:  DOLE-CARESS9');
                $sheet->mergeCells('B11:F11');
                $sheet->setCellValue('B11', 'ADDRESS: ZAMBOANGA CITY');
                foreach (['B10', 'B11'] as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // ── Column header row 12 ──────────────────────────────────
                $headerRow = 12;
                $cols = ['B' => 'NO.', 'C' => 'NAME', 'D' => 'DESIGNATION', 'E' => 'AMOUNT', 'F' => 'REMARKS'];
                foreach ($cols as $col => $label) {
                    $sheet->setCellValue("{$col}{$headerRow}", $label);
                }
                $sheet->getStyle("B{$headerRow}:F{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);

                // ── Data rows ─────────────────────────────────────────────
                $numFmt = '#,##0.00';
                $no     = 1;
                foreach ($this->rows as $row) {
                    $r = $headerRow + $no;
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $sheet->setCellValue("B{$r}", $no);
                    $sheet->setCellValue("C{$r}", $row['name']);
                    $sheet->setCellValue("D{$r}", $row['designation']);
                    $sheet->setCellValue("E{$r}", $row['amount']);
                    $sheet->setCellValue("F{$r}", '');

                    $sheet->getStyle("B{$r}:F{$r}")->applyFromArray([
                        'font'      => ['name' => 'Arial', 'size' => 10],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Alternating row shading
                    if ($no % 2 === 0) {
                        $sheet->getStyle("B{$r}:F{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F2F2F2');
                    }
                    $no++;
                }

                // ── Total Remittance row ──────────────────────────────────
                $totalRow = $headerRow + $count + 1;
                $sheet->getRowDimension($totalRow)->setRowHeight(20);
                $sheet->mergeCells("B{$totalRow}:D{$totalRow}");
                $sheet->setCellValue("B{$totalRow}", 'Total Remittance');
                $firstData = $headerRow + 1;
                $lastData  = $headerRow + $count;
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$firstData}:E{$lastData})");
                $sheet->getStyle("B{$totalRow}:F{$totalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$totalRow}")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("E{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ── Signature block ───────────────────────────────────────
                $sigRow  = $totalRow + 3;
                $nameRow = $sigRow + 4;
                $sheet->setCellValue("B{$sigRow}", 'Prepared by:');
                $sheet->setCellValue("D{$sigRow}", 'Certified by:');
                $sheet->getStyle("B{$sigRow}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getStyle("D{$sigRow}")->getFont()->setName('Arial')->setSize(10);

                // Signature lines
                $sheet->getStyle("B{$nameRow}:C{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("D{$nameRow}:F{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                $sheet->setCellValue("B" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("B" . ($nameRow + 2), 'Payroll-in-Charge');
                $sheet->setCellValue("D" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("D" . ($nameRow + 2), 'Position');
                $sheet->setCellValue("D" . ($nameRow + 3), 'HRMO / HRMO Designate');
                foreach ([$nameRow + 1, $nameRow + 2, $nameRow + 3] as $r) {
                    $sheet->getStyle("B{$r}")->getFont()->setName('Arial')->setSize(10)->setBold($r === $nameRow + 1);
                    $sheet->getStyle("D{$r}")->getFont()->setName('Arial')->setSize(10)->setBold($r === $nameRow + 1);
                }

                // ── Page setup ───────────────────────────────────────────
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

                // Apply Arial 10 globally as fallback
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
            },
        ];
    }
}

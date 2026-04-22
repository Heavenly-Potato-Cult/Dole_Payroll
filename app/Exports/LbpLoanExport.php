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

class LbpLoanExport implements FromCollection, WithTitle, WithColumnWidths, WithEvents
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

        $deductionTypeId = \App\Models\DeductionType::where('code', 'LBP_LOAN')->value('id');

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
                    'name'   => strtoupper($emp->last_name . ', ' . $emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')),
                    'amount' => $ded->amount,
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
        return 'LBP ' . $this->year . ' ' . date('M', mktime(0, 0, 0, $this->month, 1));
    }

    public function columnWidths(): array
    {
        // A=NO, B=NAME (merged B:C), C=cont, D=AMOUNT
        return ['A' => 5.5, 'B' => 40.0, 'C' => 18.0, 'D' => 16.0, 'E' => 4.0];
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
                foreach (range(2, 8) as $r) $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->getRowDimension(10)->setRowHeight(26);

                // ── LOGOS ────────────────────────────────────────────────
                $logoLeft = new Drawing();
                $logoLeft->setName('DOLE Logo');
                $logoLeft->setDescription('DOLE Logo');
                $logoLeft->setPath(public_path('assets/img/dole_logo.png'));
                $logoLeft->setHeight(60);
                $logoLeft->setCoordinates('A1');
                $logoLeft->setOffsetX(2);
                $logoLeft->setOffsetY(2);
                $logoLeft->setWorksheet($sheet);

                $logoRight = new Drawing();
                $logoRight->setName('Bagong Pilipinas');
                $logoRight->setDescription('Bagong Pilipinas Logo');
                $logoRight->setPath(public_path('assets/img/bagong_pilipinas_logo.png'));
                $logoRight->setHeight(60);
                $logoRight->setCoordinates('D1');
                $logoRight->setOffsetX(2);
                $logoRight->setOffsetY(2);
                $logoRight->setWorksheet($sheet);

                // ── Agency header (rows 1–5, merged A:D) ──────────────────
                $agencyHeaders = [
                    1 => ['Republic of the Philippines',                  false, 11],
                    2 => ['DEPARTMENT OF LABOR AND EMPLOYMENT',           true,  13],
                    3 => ['Regional Office No. IX',                       false, 11],
                    4 => ['Cortez Building, Dr. Evangelista Street',      false, 10],
                    5 => ['Barangay Sta. Catalina, Zamboanga City',       false, 10],
                ];
                foreach ($agencyHeaders as $r => [$text, $bold, $sz]) {
                    $sheet->mergeCells("A{$r}:D{$r}");
                    $sheet->setCellValue("A{$r}", $text);
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => $sz],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // ── Document title (rows 7–8) ─────────────────────────────
                $sheet->mergeCells('A7:D7');
                $sheet->setCellValue('A7', 'LANDBANK OF THE PHILIPPINES LOANS');
                $sheet->mergeCells('A8:D8');
                $sheet->setCellValue('A8', 'FOR THE MONTH OF ' . strtoupper($monthName) . ' ' . $this->year);
                foreach (['A7', 'A8'] as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 13],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ── Column header row 10 ──────────────────────────────────
                $headerRow = 10;
                $sheet->setCellValue("A{$headerRow}", 'NO.');
                $sheet->mergeCells("B{$headerRow}:C{$headerRow}");
                $sheet->setCellValue("B{$headerRow}", 'NAME');
                $sheet->setCellValue("D{$headerRow}", 'AMOUNT');
                $sheet->getStyle("A{$headerRow}:D{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);

                // ── Data rows ─────────────────────────────────────────────
                $numFmt    = '#,##0.00';
                $dataStart = 11;
                foreach ($this->rows as $idx => $row) {
                    $r  = $dataStart + $idx;
                    $no = $idx + 1;
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $sheet->setCellValue("A{$r}", $no);
                    $sheet->mergeCells("B{$r}:C{$r}");
                    $sheet->setCellValue("B{$r}", $row['name']);
                    $sheet->setCellValue("D{$r}", $row['amount']);

                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'font'    => ['name' => 'Arial', 'size' => 10],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    if ($no % 2 === 0) {
                        $sheet->getStyle("A{$r}:D{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                    }
                }

                // ── Grand Total ───────────────────────────────────────────
                $lastData = $dataStart + $count - 1;
                $totalRow = $dataStart + $count;
                $sheet->getRowDimension($totalRow)->setRowHeight(20);
                $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'GRAND TOTAL');
                $sheet->setCellValue("D{$totalRow}", "=SUM(D{$dataStart}:D{$lastData})");
                $sheet->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D{$totalRow}")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("D{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ── Signature block ───────────────────────────────────────
                $sigRow  = $totalRow + 3;
                $nameRow = $sigRow + 4;
                $sheet->setCellValue("A{$sigRow}", 'PREPARED BY:');
                $sheet->setCellValue("C{$sigRow}", 'CERTIFIED BY:');
                $sheet->getStyle("A{$sigRow}:D{$sigRow}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getStyle("A{$nameRow}:B{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("C{$nameRow}:D{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue("A" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("A" . ($nameRow + 2), 'Payroll-in-charge');
                $sheet->setCellValue("C" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("C" . ($nameRow + 2), 'Position');
                $sheet->setCellValue("C" . ($nameRow + 3), 'HRMO / HRMO Designate');
                foreach (['A', 'C'] as $col) {
                    $sheet->getStyle("{$col}" . ($nameRow + 1))->getFont()->setName('Arial')->setSize(10)->setBold(true);
                    $sheet->getStyle("{$col}" . ($nameRow + 2))->getFont()->setName('Arial')->setSize(10);
                    $sheet->getStyle("{$col}" . ($nameRow + 3))->getFont()->setName('Arial')->setSize(10);
                }

                // ── Page setup ───────────────────────────────────────────
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER)
                    ->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
            },
        ];
    }
}

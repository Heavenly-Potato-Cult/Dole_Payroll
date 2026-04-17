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

class ProvidentFundExport implements FromCollection, WithTitle, WithColumnWidths, WithEvents
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

        $deductionTypeId = \App\Models\DeductionType::where('code', 'PROVIDENT_FUND')->value('id');

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
                    'name'     => strtoupper($emp->last_name . ', ' . $emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : '')),
                    'position' => $emp->position ?? '',
                    'amount'   => $ded->amount,
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
        return 'PF ' . $this->year . ' ' . date('M', mktime(0, 0, 0, $this->month, 1));
    }

    public function columnWidths(): array
    {
        // A=spacer, B=NO, C=NAME, D=POSITION, E=AMOUNT
        return ['A' => 1.5, 'B' => 6.0, 'C' => 38.0, 'D' => 28.0, 'E' => 16.0, 'F' => 1.5];
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
                foreach (range(2, 14) as $r) $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->getRowDimension(15)->setRowHeight(22);

                // ── LOGOS ────────────────────────────────────────────────
                $logoLeft = new Drawing();
                $logoLeft->setName('Bagong Pilipinas')->setDescription('Bagong Pilipinas Logo')
                    ->setPath(public_path('assets/img/bagong_pilipinas_logo.png'))
                    ->setHeight(60)->setCoordinates('B1')->setOffsetX(2)->setOffsetY(2)
                    ->setWorksheet($sheet);

                $logoRight = new Drawing();
                $logoRight->setName('DOLE Logo')->setDescription('DOLE Logo')
                    ->setPath(public_path('assets/img/dole_logo.png'))
                    ->setHeight(60)->setCoordinates('E1')->setOffsetX(2)->setOffsetY(2)
                    ->setWorksheet($sheet);

                // ── Agency header (rows 1–5, merged B:E) ──────────────────
                $agencyHeaders = [
                    1 => ['Republic of the Philippines',                  false, 11],
                    2 => ['DEPARTMENT OF LABOR AND EMPLOYMENT',           true,  13],
                    3 => ['Regional Office No. IX',                       false, 11],
                    4 => ['Cortez Building, Dr. Evangelista Street',      false, 10],
                    5 => ['Barangay Sta. Catalina, Zamboanga City',       false, 10],
                ];
                foreach ($agencyHeaders as $r => [$text, $bold, $sz]) {
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->setCellValue("B{$r}", $text);
                    $sheet->getStyle("B{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => $sz],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // ── Document title (rows 7–9) ─────────────────────────────
                $titleLines = [
                    7 => ["MEMBERS' MONTHLY CONTRIBUTION FOR",   true,  12],
                    8 => ['DOLE PROVIDENT FUND',                  true,  13],
                    9 => ['FOR THE MONTH OF ' . strtoupper($monthName) . ' ' . $this->year, true, 12],
                ];
                foreach ($titleLines as $r => [$text, $bold, $sz]) {
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->setCellValue("B{$r}", $text);
                    $sheet->getStyle("B{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => $sz],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ── Payee block (rows 11–14) ──────────────────────────────
                $payeeLines = [
                    11 => 'PAYEE:  DEPARTMENT OF LABOR AND EMPLOYMENT PROVIDENT',
                    12 => 'FUND (DOLEPFI), INC.',
                    13 => 'ACCOUNT NUMBER: 2471-0431-01 (Land Bank of the Philippines)',
                    14 => 'ADDRESS:  Intramuros, Manila',
                ];
                foreach ($payeeLines as $r => $text) {
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->setCellValue("B{$r}", $text);
                    $sheet->getStyle("B{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // ── Column header row 15 ──────────────────────────────────
                $headerRow = 15;
                $cols = ['B' => 'NO.', 'C' => 'NAME', 'D' => 'POSITION', 'E' => 'AMOUNT'];
                foreach ($cols as $col => $label) $sheet->setCellValue("{$col}{$headerRow}", $label);
                $sheet->getStyle("B{$headerRow}:E{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);

                // ── Data rows ─────────────────────────────────────────────
                $numFmt    = '#,##0.00';
                $dataStart = 16;
                foreach ($this->rows as $idx => $row) {
                    $r  = $dataStart + $idx;
                    $no = $idx + 1;
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $sheet->setCellValue("B{$r}", $no);
                    $sheet->setCellValue("C{$r}", $row['name']);
                    $sheet->setCellValue("D{$r}", $row['position']);
                    $sheet->setCellValue("E{$r}", $row['amount']);

                    $sheet->getStyle("B{$r}:E{$r}")->applyFromArray([
                        'font'    => ['name' => 'Arial', 'size' => 10],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    if ($no % 2 === 0) {
                        $sheet->getStyle("B{$r}:E{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                    }
                }

                // ── Grand Total ───────────────────────────────────────────
                $lastData = $dataStart + $count - 1;
                $totalRow = $dataStart + $count;
                $sheet->getRowDimension($totalRow)->setRowHeight(20);
                $sheet->mergeCells("B{$totalRow}:D{$totalRow}");
                $sheet->setCellValue("B{$totalRow}", 'GRAND TOTAL');
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$dataStart}:E{$lastData})");
                $sheet->getStyle("B{$totalRow}:E{$totalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$totalRow}")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("E{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ── Signature block ───────────────────────────────────────
                $sigRow  = $totalRow + 3;
                $nameRow = $sigRow + 4;
                $sheet->setCellValue("B{$sigRow}", 'PREPARED BY:');
                $sheet->setCellValue("D{$sigRow}", 'CERTIFIED BY:');
                $sheet->getStyle("B{$sigRow}:E{$sigRow}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getStyle("B{$nameRow}:C{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("D{$nameRow}:E{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue("B" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("B" . ($nameRow + 2), 'Position, Payroll-in-charge');
                $sheet->setCellValue("D" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("D" . ($nameRow + 2), 'IMSD Chief');
                $sheet->setCellValue("D" . ($nameRow + 3), 'Internal Management Service Division');
                foreach (['B', 'D'] as $col) {
                    $sheet->getStyle("{$col}" . ($nameRow + 1))->getFont()->setName('Arial')->setSize(10)->setBold(true);
                    $sheet->getStyle("{$col}" . ($nameRow + 2))->getFont()->setName('Arial')->setSize(10);
                }
                $sheet->getStyle("D" . ($nameRow + 3))->getFont()->setName('Arial')->setSize(10);

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

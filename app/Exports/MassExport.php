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

class MassExport implements FromCollection, WithTitle, WithColumnWidths, WithEvents
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

        $deductionTypeId = \App\Models\DeductionType::where('code', 'MASS')->value('id');

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
                    's_name'  => strtoupper($emp->last_name),
                    'f_name'  => strtoupper($emp->first_name),
                    'mi'      => strtoupper($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : ''),
                    'remarks' => '',
                    'amount'  => $ded->amount,
                ];
            })
            ->sortBy('s_name')
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
        return date('Y M', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    public function columnWidths(): array
    {
        // A=spacer, B=S-NAME, C=F-NAME, D=MI, E=REMARKS, F=AMOUNT
        return ['A' => 1.5, 'B' => 24.0, 'C' => 24.0, 'D' => 6.0, 'E' => 14.0, 'F' => 14.0, 'G' => 1.5];
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
                foreach (range(2, 11) as $r) $sheet->getRowDimension($r)->setRowHeight(15);
                $sheet->getRowDimension(12)->setRowHeight(22);

                // ── LOGOS ────────────────────────────────────────────────
                $logoLeft = new Drawing();
                $logoLeft->setName('Bagong Pilipinas')->setDescription('Bagong Pilipinas Logo')
                    ->setPath(public_path('assets/img/bagong_pilipinas_logo.png'))
                    ->setHeight(60)->setCoordinates('B1')->setOffsetX(2)->setOffsetY(2)
                    ->setWorksheet($sheet);

                $logoRight = new Drawing();
                $logoRight->setName('DOLE Logo')->setDescription('DOLE Logo')
                    ->setPath(public_path('assets/img/dole_logo.png'))
                    ->setHeight(60)->setCoordinates('F1')->setOffsetX(2)->setOffsetY(2)
                    ->setWorksheet($sheet);

                // ── Agency header (rows 1–5, merged B:F) ──────────────────
                $agencyHeaders = [
                    1 => ['Republic of the Philippines',                  false, 11],
                    2 => ['DEPARTMENT OF LABOR AND EMPLOYMENT',           true,  13],
                    3 => ['Regional Office No. IX',                       false, 11],
                    4 => ['Cortez Building, Dr. Evangelista Street',      false, 10],
                    5 => ['Barangay Sta. Catalina, Zamboanga City',       false, 10],
                ];
                foreach ($agencyHeaders as $r => [$text, $bold, $sz]) {
                    $sheet->mergeCells("B{$r}:F{$r}");
                    $sheet->setCellValue("B{$r}", $text);
                    $sheet->getStyle("B{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => $sz],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // ── Payee block (rows 7–11) ───────────────────────────────
                $payeeBlock = [
                    7  => ['PAYEE:',            'WARREN M. MICLAT AND MARIA TERESA M. CABANCE', true],
                    8  => ['ADDRESS:',          'C/O Bureau of Labor Relations',                 true],
                    9  => ['',                  '4th Floor, B.F. Homes Condominium, Intramuros, Manila', false],
                    10 => ['PERIOD COVERED:',   "{$monthName} {$this->year}",                   true],
                ];
                foreach ($payeeBlock as $r => [$label, $value, $bold]) {
                    $sheet->setCellValue("B{$r}", $label);
                    $sheet->mergeCells("C{$r}:F{$r}");
                    $sheet->setCellValue("C{$r}", $value);
                    $sheet->getStyle("B{$r}")->getFont()->setName('Arial')->setSize(10);
                    $sheet->getStyle("C{$r}")->applyFromArray([
                        'font'      => ['bold' => $bold, 'name' => 'Arial', 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // ── Column header row 12 ──────────────────────────────────
                $headerRow = 12;
                $cols = ['B' => 'S-NAME', 'C' => 'F-NAME', 'D' => 'MI', 'E' => 'REMARKS', 'F' => 'AMOUNT'];
                foreach ($cols as $col => $label) $sheet->setCellValue("{$col}{$headerRow}", $label);
                $sheet->getStyle("B{$headerRow}:F{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);

                // ── Data rows ─────────────────────────────────────────────
                $numFmt    = '#,##0.00';
                $dataStart = 13;
                foreach ($this->rows as $idx => $row) {
                    $r  = $dataStart + $idx;
                    $no = $idx + 1;
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $sheet->setCellValue("B{$r}", $row['s_name']);
                    $sheet->setCellValue("C{$r}", $row['f_name']);
                    $sheet->setCellValue("D{$r}", $row['mi']);
                    $sheet->setCellValue("E{$r}", $row['remarks']);
                    $sheet->setCellValue("F{$r}", $row['amount']);

                    $sheet->getStyle("B{$r}:F{$r}")->applyFromArray([
                        'font'    => ['name' => 'Arial', 'size' => 10],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    if ($no % 2 === 0) {
                        $sheet->getStyle("B{$r}:F{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                    }
                }

                // ── Grand Total ───────────────────────────────────────────
                $lastData = $dataStart + $count - 1;
                $totalRow = $dataStart + $count;
                $sheet->getRowDimension($totalRow)->setRowHeight(20);
                $sheet->mergeCells("B{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("B{$totalRow}", 'GRAND TOTAL');
                $sheet->setCellValue("F{$totalRow}", "=SUM(F{$dataStart}:F{$lastData})");
                $sheet->getStyle("B{$totalRow}:F{$totalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("F{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ── Signature block ───────────────────────────────────────
                $sigRow  = $totalRow + 3;
                $nameRow = $sigRow + 4;
                $sheet->setCellValue("B{$sigRow}", 'CERTIFIED  BY:');
                $sheet->getStyle("B{$sigRow}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getStyle("B{$nameRow}:D{$nameRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue("B" . ($nameRow + 1), 'NAME');
                $sheet->setCellValue("B" . ($nameRow + 2), 'Position, HRMO / HRMO Designate');
                $sheet->getStyle("B" . ($nameRow + 1))->getFont()->setName('Arial')->setSize(10)->setBold(true);
                $sheet->getStyle("B" . ($nameRow + 2))->getFont()->setName('Arial')->setSize(10);

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

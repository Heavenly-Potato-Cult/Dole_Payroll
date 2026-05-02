<?php

namespace Modules\Payroll\Exports;

use Modules\Payroll\Models\PayrollEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class HdmfHousingExport implements FromCollection, WithTitle, WithEvents
{
    private const EMPLOYER_ID   = '206587760004';
    private const EMPLOYER_NAME = 'DEPARTMENT OF LABOR & EMPLOYMENT - Regional Office IX';
    private const ADDRESS       = 'Sta. Catalina ZC';
    private const LOAN_TYPE     = 'HL';
    private const POST_CODE     = 'R - Regular Amortization';
    private const CODE = 'HDMF_HOUSING';
    private const LAST_COL      = 'J';

    private int    $year;
    private int    $month;
    private string $cutoff;
    private array  $rows = [];

    public function __construct(int $year, int $month, string $cutoff = 'both')
    {
        $this->year   = $year;
        $this->month  = $month;
        $this->cutoff = $cutoff;
        $this->compute();
    }

    private function compute(): void
    {
        $entries = PayrollEntry::query()
            ->with(['employee', 'deductions' => fn($q) => $q->where('code', self::CODE)])
            ->whereHas('batch', function ($q) {
                $q->whereYear('period_start', $this->year)
                  ->whereMonth('period_start', $this->month);
                if ($this->cutoff === '1st') $q->where('cutoff', '1st');
                elseif ($this->cutoff === '2nd') $q->where('cutoff', '2nd');
            })
            ->whereHas('deductions', fn($q) => $q->where('code', self::CODE)->where('amount', '>', 0))
            ->get();

        foreach ($entries as $entry) {
            $ded = $entry->deductions->first();
            $emp = $entry->employee;
            $this->rows[] = [
                $emp->pagibig_id          ?? '',   // col A - Pag-IBIG ID / RTN
                $emp->hdmf_housing_app_no ?? '',   // col B - APPLICATION NO / AGREEMENT NO
                $emp->last_name           ?? '',   // col C
                $emp->first_name          ?? '',   // col D
                $emp->name_extension      ?? '',   // col E
                $emp->middle_name         ?? '',   // col F
                // col G - LOAN TYPE filled directly
                // col H - POST CODE filled directly
                round((float)($ded->amount ?? 0), 2), // col I - AMOUNT
                '',                                 // col J - REMARKS
            ];
        }
    }

    public function collection(): Collection { return collect([]); }

    public function title(): string { return 'HDMF Housing'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn(AfterSheet $e) => $this->buildSheet($e->sheet->getDelegate()),
        ];
    }

    private function thinBorder(): array
    {
        return [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
    }

    private function buildSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws): void
    {
        // ── Rows 1-3: Employer info ──────────────────────────────────────
        $ws->setCellValue('A1', 'Employer ID');
        $ws->getCell('B1')->setValueExplicit(self::EMPLOYER_ID, DataType::TYPE_STRING);
        $ws->getStyle('B1')->getFont()->setBold(true);
        $ws->setCellValue('A2', 'Employer Name');
        $ws->setCellValue('B2', self::EMPLOYER_NAME);
        $ws->setCellValue('A3', 'Address');
        $ws->setCellValue('B3', self::ADDRESS);
        // Row 4 intentionally blank

        // ── Row 5: Column headers ─────────────────────────────────────────
        $headers = [
            'A5' => "Pag-IBIG ID /\nRTN",
            'B5' => "APPLICATION NO /\nAGREEMENT NO",
            'C5' => "LAST\nNAME",
            'D5' => "FIRST\nNAME",
            'E5' => "NAME\nEXTENSION",
            'F5' => "MIDDLE\nNAME",
            'G5' => 'LOAN TYPE',
            'H5' => 'POST CODE',
            'I5' => 'AMOUNT',
            'J5' => 'REMARKS',
        ];
        foreach ($headers as $coord => $label) {
            $ws->setCellValue($coord, $label);
        }
        $ws->getStyle('A5:' . self::LAST_COL . '5')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true],
        ]);
        $ws->getStyle('A5:' . self::LAST_COL . '5')->applyFromArray($this->thinBorder());
        $ws->getRowDimension(5)->setRowHeight(39.95);

        // ── Data rows starting at row 6 ───────────────────────────────────
        $r = 6;
        foreach ($this->rows as $row) {
            $ws->setCellValueByColumnAndRow(1,  $r, $row[0]);
            $ws->setCellValueByColumnAndRow(2,  $r, $row[1]);
            $ws->setCellValueByColumnAndRow(3,  $r, $row[2]);
            $ws->setCellValueByColumnAndRow(4,  $r, $row[3]);
            $ws->setCellValueByColumnAndRow(5,  $r, $row[4]);
            $ws->setCellValueByColumnAndRow(6,  $r, $row[5]);
            // LOAN TYPE and POST CODE — not bold for Housing (matches template)
            $ws->setCellValueByColumnAndRow(7,  $r, self::LOAN_TYPE);
            $ws->setCellValueByColumnAndRow(8,  $r, self::POST_CODE);
            $ws->setCellValueByColumnAndRow(9,  $r, $row[6]);
            $ws->getStyleByColumnAndRow(9, $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->setCellValueByColumnAndRow(10, $r, $row[7]);
            $ws->getStyle('A' . $r . ':' . self::LAST_COL . $r)->applyFromArray($this->thinBorder());
            $ws->getRowDimension($r)->setRowHeight(21.95);
            $r++;
        }

        // ── Total row ─────────────────────────────────────────────────────
        $dataStart = 6;
        $dataEnd   = $r - 1;
        $ws->setCellValue("A{$r}", 'TOTAL REMITTANCE');
        $ws->getStyle("A{$r}")->getFont()->setBold(true);
        if ($dataEnd >= $dataStart) {
            $ws->setCellValue("I{$r}", "=SUM(I{$dataStart}:I{$dataEnd})");
        } else {
            $ws->setCellValue("I{$r}", 0);
        }
        $ws->getStyle("I{$r}")->getFont()->setBold(true);
        $ws->getStyle("I{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getRowDimension($r)->setRowHeight(21.95);
        $r += 2;

        // ── Signature block ───────────────────────────────────────────────
        $ws->setCellValue("A{$r}", 'Prepared by:');
        $ws->setCellValue("E{$r}", 'Certified By:');
        $r += 2;
        $ws->setCellValue("A{$r}", 'NAME');
        $ws->setCellValue("E{$r}", 'NAME');
        $r++;
        $ws->setCellValue("A{$r}", 'HRMO / HRMO Designate');
        $ws->setCellValue("E{$r}", 'IMSD Head');
        $r++;
        $ws->setCellValue("E{$r}", 'Internal Management Service Division');

        // ── Column widths (from DOLE template) ────────────────────────────
        foreach (['A' => 18.71, 'B' => 20.71, 'C' => 16.71, 'D' => 13.71,
                  'E' => 12.71, 'F' => 12.71, 'G' => 8.71, 'H' => 25.71,
                  'I' => 11.71, 'J' => 12.71] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }
    }

    public function getRows(): array  { return $this->rows; }
    public function getTotal(): float { return array_sum(array_column($this->rows, 6)); }
    public function getCount(): int   { return count($this->rows); }
}

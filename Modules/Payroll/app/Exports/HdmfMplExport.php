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

class HdmfMplExport implements FromCollection, WithTitle, WithEvents
{
    private const EMPLOYER_ID   = '206587760004';
    private const EMPLOYER_NAME = 'DEPARTMENT OF LABOR & EMPLOYMENT - Regional Office IX';
    private const ADDRESS       = 'Sta. Catalina ZC';
    private const FORM_REF      = "HQP-SLF-017\n(V03, 10/2019)";
    private const LOAN_TYPE     = 'MPL';
    private const CODE          = 'HDMF_MPL';
    private const LAST_COL      = 'I';

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
                $emp->pagibig_id      ?? '',   // col A - Pag-IBIG ID
                $emp->hdmf_mpl_app_no ?? '',   // col B - APPLICATION NUMBER
                $emp->last_name       ?? '',   // col C
                $emp->first_name      ?? '',   // col D
                $emp->name_extension  ?? '',   // col E
                $emp->middle_name     ?? '',   // col F
                // col G - LOAN TYPE filled directly (bold per template)
                round((float)($ded->amount ?? 0), 2), // col H - EE SHARE
                '',                             // col I - REMARKS
            ];
        }
    }

    public function collection(): Collection { return collect([]); }

    public function title(): string { return 'HDMF MPL'; }

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
        // Form reference top-right — col I row 1 (same column as CAL; both sheets are identical layout)
        $ws->setCellValue('I1', self::FORM_REF);
        $ws->getStyle('I1')->getAlignment()->setWrapText(true);
        $ws->setCellValue('A2', 'Employer Name');
        $ws->setCellValue('B2', self::EMPLOYER_NAME);
        $ws->setCellValue('A3', 'Address');
        $ws->setCellValue('B3', self::ADDRESS);
        // Row 4 intentionally blank

        // ── Row 5: Column headers ─────────────────────────────────────────
        $headers = [
            'A5' => 'Pag-IBIG ID',
            'B5' => "APPLICATION\nNUMBER",
            'C5' => "LAST\nNAME",
            'D5' => "FIRST\nNAME",
            'E5' => 'NAME EXTENSION',
            'F5' => "MIDDLE\nNAME",
            'G5' => "LOAN\nTYPE",
            'H5' => "EE\nSHARE",
            'I5' => 'REMARKS',
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
            $ws->setCellValueByColumnAndRow(1, $r, $row[0]);
            $ws->setCellValueByColumnAndRow(2, $r, $row[1]);
            $ws->setCellValueByColumnAndRow(3, $r, $row[2]);
            $ws->setCellValueByColumnAndRow(4, $r, $row[3]);
            $ws->setCellValueByColumnAndRow(5, $r, $row[4]);
            $ws->setCellValueByColumnAndRow(6, $r, $row[5]);
            // LOAN TYPE — bold per DOLE template
            $ws->setCellValueByColumnAndRow(7, $r, self::LOAN_TYPE);
            $ws->getStyleByColumnAndRow(7, $r)->getFont()->setBold(true);
            $ws->getStyleByColumnAndRow(7, $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->setCellValueByColumnAndRow(8, $r, $row[6]);
            $ws->getStyleByColumnAndRow(8, $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->setCellValueByColumnAndRow(9, $r, $row[7]);
            $ws->getStyle('A' . $r . ':' . self::LAST_COL . $r)->applyFromArray($this->thinBorder());
            $ws->getRowDimension($r)->setRowHeight(24.95);
            $r++;
        }

        // ── Total row ─────────────────────────────────────────────────────
        $dataStart = 6;
        $dataEnd   = $r - 1;
        $ws->setCellValue("A{$r}", 'TOTAL REMITTANCE');
        $ws->getStyle("A{$r}")->getFont()->setBold(true);
        if ($dataEnd >= $dataStart) {
            $ws->setCellValue("H{$r}", "=SUM(H{$dataStart}:H{$dataEnd})");
        } else {
            $ws->setCellValue("H{$r}", 0);
        }
        $ws->getStyle("H{$r}")->getFont()->setBold(true);
        $ws->getStyle("H{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getRowDimension($r)->setRowHeight(24.95);
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
        foreach (['A' => 16.71, 'B' => 20.71, 'C' => 16.71, 'D' => 20.71,
                  'E' => 13.71, 'F' => 15.71, 'G' => 10.71, 'H' => 13.71, 'I' => 11.71] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }
    }

    public function getRows(): array  { return $this->rows; }
    public function getTotal(): float { return array_sum(array_column($this->rows, 6)); }
    public function getCount(): int   { return count($this->rows); }
}

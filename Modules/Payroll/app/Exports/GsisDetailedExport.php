<?php

namespace Modules\Payroll\Exports;

use App\SharedKernel\Models\Employee;
use Modules\Payroll\Models\PayrollDeduction;
use Modules\Payroll\Models\PayrollEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GsisDetailedExport implements FromCollection, WithTitle, WithEvents
{
    /**
     * GSIS Detailed column map.
     * Key   = GSIS column header (exact, matches 02-GSIS-Remittance.xls row 5)
     * Value = payroll_deductions.code from DeductionTypeSeeder, or null if
     *         GSIS-maintained (left blank; agency does not remit this column).
     *
     * Column order is fixed by GSIS — do NOT reorder.
     */
    private const COLUMN_MAP = [
        'PS'             => 'GSIS_LIFE_RETIREMENT',  // Personal Share
        'GS'             => null,                     // Govt Share — employer, not in payroll_deductions
        'EC'             => null,                     // EC Premium — employer, not in payroll_deductions
        'CONSO LOAN'     => 'GSIS_CONSO',
        'ECARDPLUS'      => null,
        'SALARY_LOAN'    => null,
        'CASH_ADV'       => null,
        'EMRGYLN'        => 'GSIS_EMERGENCY',
        'EDUC ASST'      => 'GSIS_EDUC',
        'ELA'            => null,
        'SOS'            => null,
        'PLREG'          => null,
        'PLOPT'          => 'GSIS_POLICY',
        'REL'            => 'GSIS_REAL_ESTATE',
        'LCH_DCS'        => null,
        'STOCK_PURCHASE' => null,
        'OPT_LIFE'       => null,
        'CEAP'           => null,
        'EDU_CHILD'      => null,
        'GENESIS'        => null,
        'GENPLUS'        => null,
        'GENFLEXI'       => null,
        'GENSPCL'        => null,
        'HELP'           => 'GSIS_HELP',
        'GFAL'           => 'GSIS_GFAL',
        'MPL'            => 'GSIS_MPL',
        'CPL'            => 'GSIS_CPL',
        'GEL'            => null,
        'MPL LITE'       => 'GSIS_MPL_LITE',
        'LRP'            => null,
    ];

    private int $year;
    private int $month;
    private string $cutoff;
    private string $dueMonth;

    /** employee_id => [gsis_header => amount] */
    private array $deductionsByEmployee = [];
    private Collection $employees;

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
        // Codes we can actually source from payroll_deductions
        $codes = array_values(array_filter(array_values(self::COLUMN_MAP)));

        // Build reverse map: deduction_code => [gsis_header, ...]
        $codeToHeaders = [];
        foreach (self::COLUMN_MAP as $header => $code) {
            if ($code !== null) {
                $codeToHeaders[$code][] = $header;
            }
        }

        // Sum deductions per employee per period
        $deductions = PayrollDeduction::query()
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
            ->with('entry:id,employee_id')
            ->get(['payroll_entry_id', 'code', 'amount']);

        foreach ($deductions as $d) {
            $empId = $d->entry->employee_id;
            foreach ($codeToHeaders[$d->code] ?? [] as $header) {
                $this->deductionsByEmployee[$empId][$header] =
                    ($this->deductionsByEmployee[$empId][$header] ?? 0) + (float) $d->amount;
            }
        }

        // All employees who appeared in any payroll batch for this period
        $employeeIds = PayrollEntry::query()
            ->whereHas('batch', function ($q) {
                $q->whereYear('period_start', $this->year)
                  ->whereMonth('period_start', $this->month);
                if ($this->cutoff === '1st') {
                    $q->where('cutoff', '1st');
                } elseif ($this->cutoff === '2nd') {
                    $q->where('cutoff', '2nd');
                }
            })
            ->pluck('employee_id')
            ->unique()
            ->values();

        $this->employees = Employee::whereIn('id', $employeeIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function collection(): Collection
    {
        return collect([]);
    }

    public function title(): string
    {
        return 'Detailed GSIS Remittance';
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
        // ── Meta rows 1–3 (matches template) ─────────────────────────────────
        $ws->setCellValue('A1', 'Remitting Agency');
        $ws->setCellValue('B1', 'DOLE, REG IX');
        $ws->setCellValue('A2', 'Office Code');
        $ws->setCellValue('B2', '1000032479');
        $ws->setCellValue('A3', 'Due Month');
        $ws->setCellValue('B3', $this->dueMonth);

        // ── Column headers row 5 ──────────────────────────────────────────────
        $fixedHeaders = [
            'BP NO', 'Last Name', 'First Name', 'MI', 'PREFIX', 'APPELLATION',
            'Birth Date', 'CRN', 'Basic Monthly Salary', 'Effectivity Date',
        ];
        $gsisHeaders = array_keys(self::COLUMN_MAP);
        $allHeaders  = array_merge($fixedHeaders, $gsisHeaders);

        foreach ($allHeaders as $i => $header) {
            $col  = Coordinate::stringFromColumnIndex($i + 1);
            $ws->setCellValue("{$col}5", $header);
            $ws->getStyle("{$col}5")->applyFromArray([
                'font'      => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'wrapText'   => true,
                ],
            ]);
        }
        $ws->getRowDimension(5)->setRowHeight(30);

        // ── Data rows from row 6 ──────────────────────────────────────────────
        $row = 6;
        foreach ($this->employees as $emp) {
            $empDed = $this->deductionsByEmployee[$emp->id] ?? [];

            $rowData = [
                $emp->bpno            ?? '',
                $emp->last_name       ?? '',
                $emp->first_name      ?? '',
                $emp->middle_initial  ?? '',
                $emp->prefix          ?? '',
                $emp->appellation     ?? '',
                $emp->birth_date
                    ? \Carbon\Carbon::parse($emp->birth_date)->format('m/d/Y')
                    : '',
                $emp->crn             ?? '',
                (float) ($emp->basic_salary ?? 0),
                $emp->effectivity_date
                    ? \Carbon\Carbon::parse($emp->effectivity_date)->format('m/d/Y')
                    : '',
            ];

            foreach ($gsisHeaders as $header) {
                $rowData[] = $empDed[$header] ?? 0;
            }

            foreach ($rowData as $i => $value) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $ws->setCellValue("{$col}{$row}", $value);
                // Format numeric columns (Basic Monthly Salary onwards)
                if ($i >= 8) {
                    $ws->getStyle("{$col}{$row}")
                       ->getNumberFormat()
                       ->setFormatCode('#,##0.00');
                }
            }

            $row++;
        }

        // ── Column widths ─────────────────────────────────────────────────────
        $fixedWidths = [12, 18, 15, 5, 8, 12, 12, 16, 18, 15];
        foreach ($fixedWidths as $i => $width) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $ws->getColumnDimension($col)->setWidth($width);
        }
        // GSIS deduction columns
        $startCol = count($fixedHeaders) + 1;
        $endCol   = count($allHeaders);
        for ($c = $startCol; $c <= $endCol; $c++) {
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(10);
        }
    }

    public function getEmployeeCount(): int
    {
        return $this->employees->count();
    }
}
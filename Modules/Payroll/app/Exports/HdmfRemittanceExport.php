<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HdmfRemittanceExport implements WithMultipleSheets
{
    private int    $year;
    private int    $month;
    private string $cutoff;

    public function __construct(int $year, int $month, string $cutoff = 'both')
    {
        $this->year   = $year;
        $this->month  = $month;
        $this->cutoff = $cutoff;
    }

    public function sheets(): array
    {
        return [
            new HdmfP1Export($this->year, $this->month, $this->cutoff),
            new HdmfP2Export($this->year, $this->month, $this->cutoff),
            new HdmfMplExport($this->year, $this->month, $this->cutoff),
            new HdmfCalExport($this->year, $this->month, $this->cutoff),
            new HdmfHousingExport($this->year, $this->month, $this->cutoff),
        ];
    }
}

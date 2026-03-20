<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement GsisSummaryExport
class GsisSummaryExport implements FromCollection {
    public function collection() { return collect([]); }
}

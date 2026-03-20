<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement GsisDetailedExport
class GsisDetailedExport implements FromCollection {
    public function collection() { return collect([]); }
}

<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement HdmfMplExport
class HdmfMplExport implements FromCollection {
    public function collection() { return collect([]); }
}

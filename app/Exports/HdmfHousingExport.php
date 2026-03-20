<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement HdmfHousingExport
class HdmfHousingExport implements FromCollection {
    public function collection() { return collect([]); }
}

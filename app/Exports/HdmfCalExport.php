<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement HdmfCalExport
class HdmfCalExport implements FromCollection {
    public function collection() { return collect([]); }
}

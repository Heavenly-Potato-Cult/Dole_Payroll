<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement HdmfP1Export
class HdmfP1Export implements FromCollection {
    public function collection() { return collect([]); }
}

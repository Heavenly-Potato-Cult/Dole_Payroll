<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
// TODO: implement HdmfP2Export
class HdmfP2Export implements FromCollection {
    public function collection() { return collect([]); }
}

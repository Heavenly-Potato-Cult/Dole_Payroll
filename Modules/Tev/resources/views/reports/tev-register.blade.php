<?php

namespace App\Exports;

use Modules\Tev\Models\TevRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TevRegisterExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(private array $filters = []) {}

    // ── Query ─────────────────────────────────────────────────────────────────
    public function query()
    {
        $query = TevRequest::with(['employee.division', 'officeOrder'])
            ->orderByDesc('travel_date_start');

        if (!empty($this->filters['year'])) {
            $query->whereYear('travel_date_start', $this->filters['year']);
        }
        if (!empty($this->filters['month'])) {
            $query->whereMonth('travel_date_start', $this->filters['month']);
        }
        if (!empty($this->filters['track'])) {
            $query->where('track', $this->filters['track']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['employee_id'])) {
            $query->where('employee_id', $this->filters['employee_id']);
        }

        return $query;
    }

    // ── Column headings ───────────────────────────────────────────────────────
    public function headings(): array
    {
        return [
            'TEV No.',
            'Employee Name',
            'Division',
            'Track',
            'Start Date',
            'End Date',
            'Days',
            'Transportation (PHP)',
            'Per Diem (PHP)',
            'Grand Total (PHP)',
            'Status',
            'OO No.',
        ];
    }

    // ── Row mapping ───────────────────────────────────────────────────────────
    public function map($tev): array
    {
        $emp = $tev->employee;

        $name = $emp
            ? trim($emp->last_name . ', ' . $emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '.' : ''))
            : '—';

        return [
            $tev->tev_no,
            $name,
            optional(optional($emp)->division)->name ?? '—',
            $tev->track === 'cash_advance' ? 'Cash Advance' : 'Reimbursement',
            $tev->travel_date_start?->format('Y-m-d') ?? '—',
            $tev->travel_date_end?->format('Y-m-d') ?? '—',
            $tev->total_days ?? 0,
            number_format($tev->total_transportation, 2, '.', ''),
            number_format($tev->total_per_diem, 2, '.', ''),
            number_format($tev->grand_total, 2, '.', ''),
            ucwords(str_replace('_', ' ', $tev->status)),
            optional($tev->officeOrder)->office_order_no ?? '—',
        ];
    }

    // ── Sheet title ───────────────────────────────────────────────────────────
    public function title(): string
    {
        return 'TEV Register';
    }

    // ── Header row styling ─────────────────────────────────────────────────────
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F1B4C']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
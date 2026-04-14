<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HrisApiService
{
    /**
     * Base URL for the HRIS API (provided by Maam Eden Cutara, TSSD).
     * Set in .env: HRIS_API_URL=https://hris.dole9.gov.ph/api
     *             HRIS_API_KEY=your-api-key-here
     */
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.hris.url', '') ?? '';
        $this->apiKey  = config('services.hris.key', '') ?? '';
    }

    /**
     * Fetch all employees from HRIS API.
     *
     * @return array {
     *   employee_id: string,
     *   employee_no: string,
     *   last_name: string,
     *   first_name: string,
     *   middle_name: string|null,
     *   position_title: string,
     *   plantilla_item_no: string,
     *   salary_grade: int,
     *   step: int,
     *   basic_monthly_salary: float,
     *   division_id: int|null,
     *   division_name: string|null,
     *   employment_status: string,
     *   official_station: string|null,
     *   date_original_appointment: string|null,
     *   last_promotion_date: string|null,
     *   gsis_bp_no: string|null,
     *   gsis_crn: string|null,
     *   pagibig_mid_no: string|null,
     *   philhealth_no: string|null,
     *   tin: string|null,
     * }
     */
    public function fetchEmployees(): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->get("{$this->baseUrl}/employees");

            if ($response->successful()) {
                $data = $response->json();
                // API returns { total: 82, data: [...] }, we need the data array
                return $data['data'] ?? [];
            }

            Log::warning('HRIS API employees non-200', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('HRIS API employees error', ['error' => $e->getMessage()]);
        }

        // Fallback to mock if API fails
        return $this->mockEmployees();
    }

    /**
     * Fetch pre-processed DTR attendance data for one employee
     * for a given cut-off period.
     *
     * @param  string $employeeId   The employee's plantilla_item_no or HRIS ID
     * @param  string $cutoffStart  Format: Y-m-d  e.g. "2026-03-01"
     * @param  string $cutoffEnd    Format: Y-m-d  e.g. "2026-03-15"
     *
     * @return array {
     *   employee_id:       string,
     *   cutoff_start:      string,
     *   cutoff_end:        string,
     *   days_present:      float,   // working days actually worked
     *   lwop_days:         float,   // Leave Without Pay days (decimal from Table IV)
     *   late_minutes:      int,     // total late minutes for the period
     *   undertime_minutes: int,     // total undertime minutes for the period
     * }
     */
    public function fetchAttendance(
        string $employeeId,
        string $cutoffStart,
        string $cutoffEnd
    ): array {
        // ── TODO: Replace mock with real API call ─────────────────
        // When Maam Eden provides the real endpoint, replace the
        // mock block below with:
        //
        // try {
        //     $response = Http::withToken($this->apiKey)
        //         ->timeout(10)
        //         ->get("{$this->baseUrl}/attendance", [
        //             'employee_id'  => $employeeId,
        //             'cutoff_start' => $cutoffStart,
        //             'cutoff_end'   => $cutoffEnd,
        //         ]);
        //
        //     if ($response->successful()) {
        //         return $response->json();
        //     }
        //
        //     Log::warning('HRIS API non-200', [
        //         'employee_id' => $employeeId,
        //         'status'      => $response->status(),
        //         'body'        => $response->body(),
        //     ]);
        // } catch (\Exception $e) {
        //     Log::error('HRIS API error', ['error' => $e->getMessage()]);
        // }
        //
        // // Fallback: return perfect attendance if API fails
        // return $this->perfectAttendance($employeeId, $cutoffStart, $cutoffEnd);
        // ─────────────────────────────────────────────────────────

        // ── MOCK RESPONSE (development stub) ─────────────────────
        return $this->mockAttendance($employeeId, $cutoffStart, $cutoffEnd);
    }

    /**
     * Mock response — simulates employee data from HRIS.
     * Used during development before the real API is available.
     */
    private function mockEmployees(): array
    {
        return [
            [
                'employee_id' => 'EMP001',
                'employee_no' => 'DOLE9-001',
                'last_name' => 'SANTOS',
                'first_name' => 'MARIA',
                'middle_name' => 'REYES',
                'position_title' => 'Administrative Aide IV',
                'plantilla_item_no' => 'DOLE9-001',
                'salary_grade' => 4,
                'step' => 1,
                'basic_monthly_salary' => 14993.00,
                'division_id' => 1,
                'division_name' => 'IMSD',
                'employment_status' => 'permanent',
                'official_station' => 'DOLE RO9 - Zamboanga City',
                'date_original_appointment' => '2015-06-01',
                'last_promotion_date' => '2020-01-15',
                'gsis_bp_no' => 'GSIS-001',
                'gsis_crn' => 'CRN-001',
                'pagibig_mid_no' => 'PAGIBIG-001',
                'philhealth_no' => 'PH-001',
                'tin' => '111-222-333-000',
            ],
            [
                'employee_id' => 'EMP002',
                'employee_no' => 'DOLE9-002',
                'last_name' => 'DELA CRUZ',
                'first_name' => 'JUAN',
                'middle_name' => 'GARCIA',
                'position_title' => 'Labor and Employment Officer II',
                'plantilla_item_no' => 'DOLE9-002',
                'salary_grade' => 15,
                'step' => 3,
                'basic_monthly_salary' => 35858.00,
                'division_id' => 2,
                'division_name' => 'TSSD',
                'employment_status' => 'permanent',
                'official_station' => 'DOLE RO9 - Zamboanga City',
                'date_original_appointment' => '2010-03-15',
                'last_promotion_date' => '2018-06-01',
                'gsis_bp_no' => 'GSIS-002',
                'gsis_crn' => 'CRN-002',
                'pagibig_mid_no' => 'PAGIBIG-002',
                'philhealth_no' => 'PH-002',
                'tin' => '111-222-333-001',
            ],
        ];
    }

    /**
     * Mock response — simulates a clean attendance record (no LWOP, no tardiness).
     * Used during development before the real API is available.
     */
    private function mockAttendance(
        string $employeeId,
        string $cutoffStart,
        string $cutoffEnd
    ): array {
        return [
            'employee_id'       => $employeeId,
            'cutoff_start'      => $cutoffStart,
            'cutoff_end'        => $cutoffEnd,
            'days_present'      => 11.0,   // typical for a 1–15 cut-off
            'lwop_days'         => 0.0,
            'late_minutes'      => 0,
            'undertime_minutes' => 0,
        ];
    }

    /**
     * Returns a perfect-attendance fallback array.
     * Used when the real API times out or returns an error.
     */
    private function perfectAttendance(
        string $employeeId,
        string $cutoffStart,
        string $cutoffEnd
    ): array {
        return [
            'employee_id'       => $employeeId,
            'cutoff_start'      => $cutoffStart,
            'cutoff_end'        => $cutoffEnd,
            'days_present'      => 11.0,
            'lwop_days'         => 0.0,
            'late_minutes'      => 0,
            'undertime_minutes' => 0,
        ];
    }
}
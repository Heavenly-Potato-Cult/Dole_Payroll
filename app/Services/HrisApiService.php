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
        $this->baseUrl = config('services.hris.url', '');
        $this->apiKey  = config('services.hris.key', '');
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
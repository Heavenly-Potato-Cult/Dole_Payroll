<?php

namespace App\Http\Controllers;

use App\Models\SalaryIndexTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryIndexTableController extends Controller
{
    /**
     * GET /api/sit?sg={sg}&step={step}&year={year}
     *
     * Returns the salary amount for the given SG / Step / Year combination.
     * Used by the AJAX sit-lookup.js on the employee form.
     *
     * Query params:
     *   sg    — integer 1–33  (required)
     *   step  — integer 1–8   (required)
     *   year  — integer       (optional; defaults to latest available year)
     *
     * Success response (200):
     *   { "amount": "48313.00", "sg": 19, "step": 1, "year": 2021 }
     *
     * Error response (404 / 422):
     *   { "error": "..." }
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sg'   => 'required|integer|min:1|max:33',
            'step' => 'required|integer|min:1|max:8',
            'year' => 'nullable|integer|min:2021',
        ]);

        $sg   = (int) $validated['sg'];
        $step = (int) $validated['step'];

        // Default to the latest year in the table when not supplied
        $year = isset($validated['year'])
            ? (int) $validated['year']
            : SalaryIndexTable::max('year');

        $record = SalaryIndexTable::where('salary_grade', $sg)
            ->where('step', $step)
            ->where('year', $year)
            ->first();

        if (! $record) {
            return response()->json([
                'error' => "No SIT record found for SG {$sg} Step {$step} CY {$year}.",
            ], 404);
        }

        return response()->json([
            'amount' => number_format($record->amount, 2, '.', ''),
            'sg'     => $record->salary_grade,
            'step'   => $record->step,
            'year'   => $record->year,
        ]);
    }
}
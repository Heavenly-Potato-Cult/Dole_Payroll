<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SalaryIndexTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryIndexTableController extends Controller
{
    /**
     * Look up a salary amount from the Salary Index Table (SIT).
     *
     * Called via AJAX by sit-lookup.js on the employee form.
     * Year defaults to the latest available year in the table if omitted.
     *
     * Query params: sg (1–33), step (1–8), year (optional)
     * Success: { "amount": "48313.00", "sg": 19, "step": 1, "year": 2021 }
     * Failure: { "error": "..." } with 404
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

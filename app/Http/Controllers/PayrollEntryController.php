<?php

namespace App\Http\Controllers;

use App\Models\PayrollBatch;
use App\Models\PayrollEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollEntryController extends Controller
{
    // ── index ─────────────────────────────────────────────────────────────
    public function index(PayrollBatch $payrollBatch)
    {
        $payrollBatch->load(['entries.employee', 'entries.deductions']);
        $entries = $payrollBatch->entries->sortBy(fn ($e) => $e->employee->last_name);
        return view('payroll.entries.index', compact('payrollBatch', 'entries'));
    }

    // ── show ──────────────────────────────────────────────────────────────
    public function show(PayrollBatch $payrollBatch, PayrollEntry $entry)
    {
        $entry->load(['employee', 'deductions.deductionType', 'batch']);
        return view('payroll.entries.show', compact('payrollBatch', 'entry'));
    }

    // ── update (manual override) ──────────────────────────────────────────
    public function update(Request $request, PayrollBatch $payrollBatch, PayrollEntry $entry)
    {
        if (!Auth::user()->hasAnyRole(['payroll_officer'])) {
            abort(403, 'Only Payroll Officers may override payroll entries.');
        }
        if ($payrollBatch->status === 'locked') {
            return back()->with('error', 'Locked payrolls cannot be edited.');
        }

        $validated = $request->validate([
            'net_amount'      => ['required', 'numeric', 'min:0'],
            'override_reason' => ['required', 'string', 'max:500'],
        ]);

        $old = $entry->net_amount;
        $entry->update(['net_amount' => $validated['net_amount']]);

        \App\Models\PayrollAuditLog::create([
            'payroll_batch_id' => $payrollBatch->id,
            'user_id'          => Auth::id(),
            'action'           => 'manual_override',
            'old_value'        => (string) $old,
            'new_value'        => (string) $validated['net_amount'],
            'ip_address'       => $request->ip(),
        ]);

        return back()->with('success', 'Entry updated and logged to audit trail.');
    }

    // ── payslip: PDF download ─────────────────────────────────────────────
    /**
     * Generate an individual payslip PDF.
     *
     * Route:  GET /payroll/{payrollBatch}/payslip/{entry}
     * Name:   payroll.payslip
     *
     * IMPORTANT: The route uses {entry} (PayrollEntry), NOT {employee}.
     * The PayrollEntry already contains employee_id; we load the employee
     * from the entry. This avoids a second route parameter and matches
     * the "Slip" link in payroll/show.blade.php.
     *
     * If you previously registered this route as:
     *   Route::get('/payroll/{batch}/payslip/{employee}', ...)
     * change it to:
     *   Route::get('/payroll/{payrollBatch}/payslip/{entry}', [PayrollEntryController::class, 'payslip'])
     *        ->name('payroll.payslip');
     */
    public function payslip(PayrollBatch $payrollBatch, PayrollEntry $entry)
    {
        $entry->load([
            'employee.division',
            'deductions',          // PayrollDeduction rows — has 'code' column
            'batch',
        ]);

        $employee = $entry->employee;
        $batch    = $payrollBatch;

        // ── Companion cut-off entry (same month/year, opposite cut-off) ───
        $companionCutoff = $batch->cutoff === '1st' ? '2nd' : '1st';

        $companionBatch = PayrollBatch::where([
            'period_year'  => $batch->period_year,
            'period_month' => $batch->period_month,
            'cutoff'       => $companionCutoff,
        ])->first();

        $companionEntry = null;
        if ($companionBatch) {
            $companionEntry = PayrollEntry::with('deductions')
                ->where('payroll_batch_id', $companionBatch->id)
                ->where('employee_id', $employee->id)
                ->first();
        }

        // Determine which is 1st and which is 2nd cut-off
        if ($batch->cutoff === '1st') {
            $entry1st = $entry;
            $entry2nd = $companionEntry;
        } else {
            $entry1st = $companionEntry;
            $entry2nd = $entry;
        }

        // Build deduction lookup maps keyed by code
        // PayrollDeduction.code = deduction_types.code (set by DeductionService)
        $ded1st = $entry1st ? $entry1st->deductions->keyBy('code') : collect();
        $ded2nd = $entry2nd ? $entry2nd->deductions->keyBy('code') : collect();

        $months = [
            '', 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        $periodLabel = ($months[$batch->period_month] ?? '') . ' 1–31, ' . $batch->period_year;

        $rows = $this->payslipRowDefinitions();

        $pdf = Pdf::loadView('payslip.show', compact(
            'employee',
            'batch',
            'entry1st',
            'entry2nd',
            'ded1st',
            'ded2nd',
            'periodLabel',
            'rows'
        ))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'dpi'                  => 96,
        ]);

        $filename = 'payslip_'
            . str_replace([' ', ',', '.'], '_', $employee->full_name)
            . "_{$batch->period_year}_{$batch->period_month}_{$batch->cutoff}.pdf";

        return $pdf->download($filename);
    }

    // ── Payslip row definitions ────────────────────────────────────────────
    /**
     * Ordered list of all rows on the payslip.
     *
     * ── CODE CONTRACT ────────────────────────────────────────────────────
     * All 'code' values here MUST match deduction_types.code in the DB.
     * Canonical list: DeductionTypeSeeder.
     * DeductionService stores these exact codes into payroll_deductions.code.
     * The $ded1st / $ded2nd maps are keyBy('code') — so lookup will only
     * work when these strings match exactly, character-for-character.
     * ─────────────────────────────────────────────────────────────────────
     *
     * 'type' values:
     *   income    → BASIC / PERA row
     *   spacer    → section header (no amount, navy background)
     *   deduction → normal deduction row
     *   sub       → indented sub-deduction (HDMF children)
     *   divider   → TOTAL row
     *   net       → NET PAY row (gold highlight)
     */
    private function payslipRowDefinitions(): array
    {
        return [
            // ── Income ─────────────────────────────────────────────────────
            ['label' => 'BASIC',          'code' => null,                   'type' => 'income'],
            ['label' => 'ALLOWANCE',      'code' => null,                   'type' => 'income'],

            // ── Section header ──────────────────────────────────────────────
            ['label' => 'DEDUCTIONS',     'code' => null,                   'type' => 'spacer'],

            // ── HDMF / Pag-IBIG ────────────────────────────────────────────
            ['label' => 'PAG-IBIG I',     'code' => 'PAG_IBIG_1',           'type' => 'deduction'],
            ['label' => 'MULTI-PURPOSE',  'code' => 'HDMF_MPL',             'type' => 'sub'],
            ['label' => 'CALAMITY LOAN',  'code' => 'HDMF_CAL',             'type' => 'sub'],
            ['label' => 'HOUSE & LOT',    'code' => 'HDMF_HOUSING',         'type' => 'sub'],
            ['label' => 'PAG-IBIG II',    'code' => 'HDMF_P2',              'type' => 'sub'],

            // ── PhilHealth & GSIS ───────────────────────────────────────────
            ['label' => 'PHILHEALTH',         'code' => 'PHILHEALTH',           'type' => 'deduction'],
            ['label' => 'LIFE/RETIREMENT',    'code' => 'GSIS_LIFE_RETIREMENT', 'type' => 'deduction'],
            ['label' => 'CONSO LOAN',         'code' => 'GSIS_CONSO',           'type' => 'deduction'],
            ['label' => 'POLICY LOAN',        'code' => 'GSIS_POLICY',          'type' => 'deduction'],
            ['label' => 'REAL ESTATE',        'code' => 'GSIS_REAL_ESTATE',     'type' => 'deduction'],
            ['label' => 'GSIS MPL',           'code' => 'GSIS_MPL',             'type' => 'deduction'],
            ['label' => 'GSIS CPL',           'code' => 'GSIS_CPL',             'type' => 'deduction'],
            ['label' => 'GSIS MPL Lite',      'code' => 'GSIS_MPL_LITE',        'type' => 'deduction'],
            ['label' => 'GFAL',               'code' => 'GSIS_GFAL',            'type' => 'deduction'],
            ['label' => 'HELP',               'code' => 'GSIS_HELP',            'type' => 'deduction'],
            ['label' => 'GSIS EMERG LOAN',    'code' => 'GSIS_EMERGENCY',       'type' => 'deduction'],

            // ── Other / Voluntary ───────────────────────────────────────────
            ['label' => 'MASS',               'code' => 'MASS',                 'type' => 'deduction'],
            ['label' => 'SSS CONTRIBUTION',   'code' => 'SSS',                  'type' => 'deduction'],
            ['label' => 'PROVIDENT FUND',     'code' => 'PROVIDENT_FUND',       'type' => 'deduction'],
            ['label' => 'W/HOLDING TAX',      'code' => 'WITHHOLDING_TAX',      'type' => 'deduction'],
            ['label' => 'LBP LOAN',           'code' => 'LBP_LOAN',             'type' => 'deduction'],
            ['label' => 'GSIS EDUCL LOAN',    'code' => 'GSIS_EDUC',            'type' => 'deduction'],
            ['label' => 'HMO',                'code' => 'HMO',                  'type' => 'deduction'],

            // ── CARESS IX ───────────────────────────────────────────────────
            ['label' => 'UNION DUES',         'code' => 'CARESS_UNION',         'type' => 'deduction'],
            ['label' => 'MORTUARY',           'code' => 'CARESS_MORTUARY',      'type' => 'deduction'],
            ['label' => 'CAREs',              'code' => 'CARESS_CARES',         'type' => 'deduction'],

            // ── Misc ────────────────────────────────────────────────────────
            ['label' => 'SMART PLAN GOLD EXCESS CHARGES', 'code' => 'SMART_PLAN_GOLD', 'type' => 'deduction'],
            ['label' => 'REFUND (VARIOUS)',   'code' => 'REFUND_VARIOUS',       'type' => 'deduction'],

            // ── Totals / Net ─────────────────────────────────────────────────
            ['label' => 'TOTAL',              'code' => null,                   'type' => 'divider'],
            ['label' => 'NET PAY 1-15',       'code' => null,                   'type' => 'net'],
            ['label' => 'NET PAY 16-30/31',   'code' => null,                   'type' => 'net'],
        ];
    }
}
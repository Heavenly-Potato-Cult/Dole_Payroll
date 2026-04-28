<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * DeductionTypeController
 *
 * CMS for managing deduction types.
 *
 * ── CRITICAL CONTRACT ────────────────────────────────────────────────────────
 * The `code` field is IMMUTABLE after creation. It is the cross-system
 * contract key used by:
 *   1. DeductionService::resolveDeductions()         — computed[] map keys
 *   2. PayrollComputationService::computeEntry()     — match() code keys
 *   3. employee_deduction_enrollments.deduction_type_id — via code lookup
 *
 * Computed types (is_computed = true) cannot have their `is_computed` flag
 * changed via the UI. Their amounts are owned by the payroll engine.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class DeductionTypeController extends Controller
{
    /** Display the full list of deduction types, grouped by category. */
    public function index()
    {
        $types = DeductionType::orderBy('display_order')
            ->orderBy('name')
            ->get();

        $grouped = $types->groupBy('category');

        $categoryLabels = self::categoryLabels();

        return view('deduction-types.index', compact('grouped', 'categoryLabels'));
    }

    /** Show the create form. */
    public function create()
    {
        $categoryLabels = self::categoryLabels();
        $nextOrder = DeductionType::max('display_order') + 1;

        return view('deduction-types.create', compact('categoryLabels', 'nextOrder'));
    }

    /** Persist a new deduction type. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'          => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_]+$/',
                'unique:deduction_types,code',
            ],
            'name'          => 'required|string|max:200',
            'category'      => ['required', Rule::in(array_keys(self::categoryLabels()))],
            'display_order' => 'required|integer|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        $data['is_computed'] = false; // User-created types are never engine-computed
        $data['is_active']   = true;

        DeductionType::create($data);

        return redirect()->route('deduction-types.index')
            ->with('success', "Deduction type \"{$data['name']}\" created successfully.");
    }

    /** Show the edit form. */
    public function edit(DeductionType $deductionType)
    {
        $categoryLabels = self::categoryLabels();

        return view('deduction-types.edit', compact('deductionType', 'categoryLabels'));
    }

    /** Update an existing deduction type. */
    public function update(Request $request, DeductionType $deductionType)
    {
        $data = $request->validate([
            // code is immutable — never updated
            'name'          => 'required|string|max:200',
            'category'      => ['required', Rule::in(array_keys(self::categoryLabels()))],
            'display_order' => 'required|integer|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        // is_computed is engine-owned — cannot be changed via UI
        $deductionType->update($data);

        return redirect()->route('deduction-types.index')
            ->with('success', "Deduction type \"{$deductionType->name}\" updated.");
    }

    /**
     * Toggle is_active on/off.
     * Computed types can be toggled too — e.g. to hide WHT for a specific setup.
     * Deactivating a type means it won't appear in enrollment forms
     * AND won't be applied in payroll computation.
     */
    public function toggle(DeductionType $deductionType)
    {
        $deductionType->update(['is_active' => ! $deductionType->is_active]);

        $state = $deductionType->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "\"{$deductionType->name}\" has been {$state}.");
    }

    /**
     * Reorder: accept a JSON array of [{ id, order }] and bulk-update display_order.
     * Called via AJAX from the index page drag-handle or up/down arrows.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items'         => 'required|array',
            'items.*.id'    => 'required|integer|exists:deduction_types,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('items') as $item) {
            DeductionType::where('id', $item['id'])
                ->update(['display_order' => $item['order']]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    public static function categoryLabels(): array
    {
        return [
            'pagibig'    => 'PAG-IBIG / HDMF',
            'philhealth' => 'PhilHealth',
            'gsis'       => 'GSIS',
            'other_gov'  => 'Government / Tax',
            'loan'       => 'Bank Loans',
            'caress'     => 'CARESS IX',
            'misc'       => 'Miscellaneous',
        ];
    }
}

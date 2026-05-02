<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Payroll\Http\Resources\DivisionResource;
use App\SharedKernel\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  Index — list all divisions (paginated)
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->input('search');

        $divisions = Division::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                                         ->orWhere('code', 'like', "%{$search}%"))
            ->withCount('employees')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('payroll::divisions.index', compact('divisions', 'search'));
    }

    // ──────────────────────────────────────────────────────────────
    //  Create — show blank form
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        return view('payroll::divisions.create');
    }

    // ──────────────────────────────────────────────────────────────
    //  Store — validate + persist
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200|unique:divisions,name',
            'code'        => 'required|string|max:20|unique:divisions,code',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Division::create($validated);

        return redirect()->route('divisions.index')
            ->with('success', 'Division "' . $validated['name'] . '" created successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    //  Show — (optional, kept for completeness / API use)
    // ──────────────────────────────────────────────────────────────
    public function show(Division $division)
    {
        $division->loadCount('employees');
        return new DivisionResource($division);
    }

    // ──────────────────────────────────────────────────────────────
    //  Edit — show pre-filled form
    // ──────────────────────────────────────────────────────────────
    public function edit(Division $division)
    {
        return view('payroll::divisions.edit', compact('division'));
    }

    // ──────────────────────────────────────────────────────────────
    //  Update — validate + persist changes
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200|unique:divisions,name,' . $division->id,
            'code'        => 'required|string|max:20|unique:divisions,code,' . $division->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $division->update($validated);

        return redirect()->route('divisions.index')
            ->with('success', 'Division "' . $division->name . '" updated successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    //  Destroy — soft-guard: block if employees are assigned
    // ──────────────────────────────────────────────────────────────
    public function destroy(Division $division)
    {
        if ($division->employees()->exists()) {
            return redirect()->route('divisions.index')
                ->with('error', 'Cannot delete "' . $division->name . '" — it still has assigned employees.');
        }

        $name = $division->name;
        $division->delete();

        return redirect()->route('divisions.index')
            ->with('success', 'Division "' . $name . '" deleted.');
    }
}

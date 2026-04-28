<?php

namespace App\Http\Controllers;

use App\Models\Signatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignatoryController extends Controller
{
    public function index()
    {
        $signatories = Signatory::orderBy('role_type')->orderByDesc('is_active')->get();

        return view('signatories.index', compact('signatories'));
    }

    public function create()
    {
        return view('signatories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_type'      => ['required', 'string', 'max:100'],
            'full_name'      => ['required', 'string', 'max:255'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'is_active'      => ['boolean'],
        ]);

        // If setting active, deactivate all others of the same role_type first
        if (! empty($validated['is_active'])) {
            Signatory::where('role_type', $validated['role_type'])
                     ->update(['is_active' => false]);
        }

        Signatory::create($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory created.');
    }

    public function edit(Signatory $signatory)
    {
        return view('signatories.edit', compact('signatory'));
    }

    public function update(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'role_type'      => ['required', 'string', 'max:100'],
            'full_name'      => ['required', 'string', 'max:255'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'is_active'      => ['boolean'],
        ]);

        if (! empty($validated['is_active'])) {
            Signatory::where('role_type', $validated['role_type'])
                     ->where('id', '!=', $signatory->id)
                     ->update(['is_active' => false]);
        }

        $signatory->update($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory updated.');
    }

    public function destroy(Signatory $signatory)
    {
        $signatory->delete();

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory removed.');
    }

    /**
     * Toggle active status.
     * Activating one automatically deactivates all others of the same role_type.
     */
    public function toggle(Signatory $signatory)
    {
        DB::transaction(function () use ($signatory) {
            if (! $signatory->is_active) {
                // Deactivate all others of the same role first
                Signatory::where('role_type', $signatory->role_type)
                         ->where('id', '!=', $signatory->id)
                         ->update(['is_active' => false]);
            }

            $signatory->update(['is_active' => ! $signatory->is_active]);
        });

        $state = $signatory->fresh()->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$signatory->full_name} {$state}.");
    }
}

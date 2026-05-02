<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\SharedKernel\Models\Signatory;
use App\Models\UserRoleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * SignatoryController
 * ─────────────────────────────────────────────────────────────────────────────
 * Manages the signing officers that appear on payslips and official reports.
 *
 * Key change from v1:
 *   Signatories are now sourced from real system users via UserRoleAssignment.
 *   The create/edit forms fetch eligible users via usersForRole() (AJAX),
 *   and full_name / position_title are optional overrides on top of the
 *   user's account name.
 *
 * Signatory role_type → system role_name mapping:
 *   hrmo_designate → hrmo
 *   accountant     → accountant
 *   ard            → ard
 *   cashier        → cashier
 */
class SignatoryController extends Controller
{
    /**
     * Maps each signatory role_type to the corresponding Spatie role name,
     * so we know which users are eligible to fill each signing role.
     */
    private const ROLE_MAP = [
        'hrmo_designate' => 'hrmo',
        'accountant'     => 'accountant',
        'ard'            => 'ard',
        'cashier'        => 'cashier',
    ];

    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $signatories = Signatory::with('user')
                                ->orderBy('role_type')
                                ->orderByDesc('is_active')
                                ->get();

        return view('payroll::signatories.index', compact('signatories'));
    }

    public function create()
    {
        return view('payroll::signatories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_type'      => ['required', 'string', 'in:' . implode(',', array_keys(self::ROLE_MAP))],
            'user_id'        => ['required', 'exists:users,id'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'full_name'      => ['nullable', 'string', 'max:255'],
            'is_active'      => ['boolean'],
        ]);

        if (!empty($validated['is_active'])) {
            Signatory::where('role_type', $validated['role_type'])
                     ->update(['is_active' => false]);
        }

        Signatory::create($validated);

        return redirect()->route('signatories.index')
            ->with('success', 'Signatory added.');
    }

    public function edit(Signatory $signatory)
    {
        $signatory->load('user');

        return view('payroll::signatories.edit', compact('signatory'));
    }

    public function update(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'role_type'      => ['required', 'string', 'in:' . implode(',', array_keys(self::ROLE_MAP))],
            'user_id'        => ['required', 'exists:users,id'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'full_name'      => ['nullable', 'string', 'max:255'],
            'is_active'      => ['boolean'],
        ]);

        if (!empty($validated['is_active'])) {
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
            if (!$signatory->is_active) {
                Signatory::where('role_type', $signatory->role_type)
                         ->where('id', '!=', $signatory->id)
                         ->update(['is_active' => false]);
            }

            $signatory->update(['is_active' => !$signatory->is_active]);
        });

        $name  = $signatory->displayName();
        $state = $signatory->fresh()->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$name} {$state}.");
    }

    // ── AJAX endpoint ─────────────────────────────────────────────────────────

    /**
     * GET /signatories/users-for-role?role_type=ard
     *
     * Returns the list of system users who hold the Spatie role that maps to
     * the requested signatory role_type. Used by the create/edit forms to
     * populate the user dropdown dynamically when the role_type changes.
     *
     * Response shape:
     * {
     *   "users": [
     *     { "id": 3, "name": "Maria Santos" },
     *     { "id": 7, "name": "Jose Reyes"   }
     *   ]
     * }
     */
    public function usersForRole(Request $request)
    {
        $roleType = $request->query('role_type');

        if (!$roleType || !array_key_exists($roleType, self::ROLE_MAP)) {
            return response()->json(['users' => []]);
        }

        $spatieRoleName = self::ROLE_MAP[$roleType];

        // Fetch users who have this Spatie role assigned
        $users = \App\Models\User::role($spatieRoleName)
                                 ->orderBy('name')
                                 ->get(['id', 'name']);

        return response()->json(['users' => $users]);
    }
}

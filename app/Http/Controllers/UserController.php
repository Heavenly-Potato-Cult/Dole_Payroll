<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Restrict the entire controller to super admins.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user || !$user->hasRole('super_admin')) {
                abort(403, 'Only Super Admins can manage system users.');
            }

            return $next($request);
        });
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $users = User::with(['roles', 'roleAssignments'])->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Create a new user, assign their Spatie role, and create the
     * UserRoleAssignment tracking row.
     *
     * If a second (alternate) role is supplied, it is also assigned via Spatie
     * and gets its own tracking row — initially inactive so it doesn't
     * interfere with the primary active officer for that role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'secondary_role'=> ['nullable', 'string', 'exists:roles,name', 'different:role'],
            'password'      => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            // ── Primary role ─────────────────────────────────────────────────
            $user->assignRole($request->role);

            UserRoleAssignment::create([
                'user_id'   => $user->id,
                'role_name' => $request->role,
                'is_active' => true,
            ]);

            // ── Secondary (alternate) role ───────────────────────────────────
            // Assigned but marked inactive by default — the Super Admin must
            // explicitly activate it via the toggle if/when the person acts
            // in that capacity.
            if ($request->filled('secondary_role')) {
                $user->assignRole($request->secondary_role);

                UserRoleAssignment::create([
                    'user_id'   => $user->id,
                    'role_name' => $request->secondary_role,
                    'is_active' => false,
                ]);
            }
        });

        return redirect()->route('users.index')
            ->with('success', "User {$request->name} created.");
    }

    public function show(User $user)
    {
        $user->load(['roles', 'roleAssignments.user']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load(['roles', 'roleAssignments']);

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update user details and role assignments.
     *
     * syncRoles() on Spatie ensures the user only holds the declared roles.
     * We mirror that by deleting removed role assignments and upserting kept ones.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'secondary_role'=> ['nullable', 'string', 'exists:roles,name', 'different:role'],
            'password'      => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->update(['name' => $request->name, 'email' => $request->email]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Build the new set of roles
            $newRoles = array_filter([
                $request->role,
                $request->secondary_role ?: null,
            ]);

            // Sync Spatie roles
            $user->syncRoles($newRoles);

            // Remove tracking rows for roles no longer assigned
            $user->roleAssignments()
                 ->whereNotIn('role_name', $newRoles)
                 ->delete();

            // Upsert tracking rows for current roles
            // Primary role: always active
            UserRoleAssignment::updateOrCreate(
                ['user_id' => $user->id, 'role_name' => $request->role],
                ['is_active' => true]
            );

            // Secondary role: keep existing is_active state; create as inactive if new
            if ($request->filled('secondary_role')) {
                UserRoleAssignment::firstOrCreate(
                    ['user_id' => $user->id, 'role_name' => $request->secondary_role],
                    ['is_active' => false]
                );
            }
        });

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} updated.");
    }

    public function destroy(User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($user->id === $authUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete(); // cascadeOnDelete handles role assignments

        return redirect()->route('users.index')
            ->with('success', "User {$name} has been removed.");
    }

    // ── Role activation toggle ────────────────────────────────────────────────

    /**
     * Toggle the is_active flag on a specific role assignment for a user.
     *
     * Activating a role for user X automatically deactivates that same role
     * for all other users — ensuring only one person is the acting officer
     * per role at any point in time.
     */
    public function activateRole(Request $request, User $user)
    {
        $request->validate([
            'role_name' => ['required', 'string', 'exists:roles,name'],
        ]);

        $roleName   = $request->role_name;
        $assignment = $user->roleAssignments()->where('role_name', $roleName)->firstOrFail();

        DB::transaction(function () use ($assignment, $roleName, $user) {
            if (!$assignment->is_active) {
                // Deactivate all other users' assignments for this role
                UserRoleAssignment::where('role_name', $roleName)
                                  ->where('user_id', '!=', $user->id)
                                  ->update(['is_active' => false]);
            }

            $assignment->update(['is_active' => !$assignment->is_active]);
        });

        $state = $assignment->fresh()->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$user->name} {$state} as {$roleName}.");
    }
}

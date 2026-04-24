<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Restrict the entire controller to super admins.
     *
     * User management is a privileged operation — only super_admin may
     * create, edit, or delete system accounts. This is enforced here rather
     * than on individual routes so there is a single, hard-to-miss gate for
     * the whole controller.
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

    /**
     * List all system users with their assigned roles.
     */
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new system user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Persist a new user and assign their initial role.
     *
     * New accounts are created with a verified email so the user can log in
     * immediately without going through an email verification flow — accounts
     * are provisioned by staff, not self-registered.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            // Staff-provisioned accounts skip email verification
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} created successfully with role: {$request->role}.");
    }

    /**
     * Display a single user's profile and role assignment.
     */
    public function show(User $user)
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing an existing user.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update a user's name, email, role, and optionally their password.
     *
     * Password is only updated when explicitly provided — leaving the field
     * blank preserves the existing credential. syncRoles() is used instead
     * of assignRole() to ensure any previously held roles are removed, keeping
     * each user to exactly one role at a time.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // syncRoles removes any previously held roles before assigning the new one
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Delete a user account.
     *
     * Self-deletion is blocked to prevent a payroll officer from accidentally
     * locking everyone out of user management by removing their own account.
     */
    public function destroy(User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($user->id === $authUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User {$name} has been removed.");
    }
}

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
     * Only payroll_officer can manage users.
     * Add this to your route group or use a policy.
     */
public function __construct()
{
    $this->middleware(function ($request, $next) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('payroll_officer')) {
            abort(403, 'Only Payroll Officers can manage system users.');
        }

        return $next($request);
    });
}

    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

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
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} created successfully with role: {$request->role}.");
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');
        return view('users.edit', compact('user', 'roles'));
    }

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

        // Only update password if a new one was provided
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Sync role (remove old, assign new)
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

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
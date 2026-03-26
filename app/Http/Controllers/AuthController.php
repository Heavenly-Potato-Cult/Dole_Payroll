<?php

namespace App\Http\Controllers;

use App\Jobs\WarmCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login form submission.
     * Uses session-based auth (Auth::attempt) — NO Sanctum tokens for web.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user()->load('roles', 'permissions');
            $userId = $user->id;

            Cache::put("auth.user.{$userId}", $user, now()->addMinutes(30));
            Cache::put("auth.user.{$userId}.roles", $user->roles, now()->addMinutes(30));
            Cache::put("auth.user.{$userId}.permissions", $user->permissions, now()->addMinutes(30));

            // Dispatch the job to warm up the cache
            WarmCache::dispatch($userId);

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CacheAuthUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cacheKey = "auth.user.{$userId}";

            // Try to get user from Redis first
            $cachedUser = Cache::get($cacheKey);

            if ($cachedUser) {
                // Swap the auth user with cached version
                Auth::setUser($cachedUser);
            } else {
                // Not in cache — fetch from DB and store in Redis
                Cache::put($cacheKey, Auth::user(), now()->addMinutes(30));
            }
        }

        return $next($request);
    }
}
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

            $cachedUser = Cache::get($cacheKey);

            if ($cachedUser) {
                Auth::setUser($cachedUser);

                // Load cached roles into Spatie so it skips the DB query
                $roles = Cache::get("auth.user.{$userId}.roles");
                if ($roles) {
                    $cachedUser->setRelation('roles', $roles);
                    $cachedUser->setRelation('permissions', 
                        Cache::get("auth.user.{$userId}.permissions", collect())
                    );
                }
            } else {
                $user = Auth::user();
                Cache::put($cacheKey, $user, now()->addMinutes(30));
            }
        }

        return $next($request);
    }
}
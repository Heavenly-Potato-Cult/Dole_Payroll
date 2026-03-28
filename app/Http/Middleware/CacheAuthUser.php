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
        $start = microtime(true);
        
        if (Auth::check()) {
            $userId = Auth::id();
            $cacheKey = "auth.user.{$userId}";

            $cacheStart = microtime(true);
            $cachedUser = Cache::get($cacheKey);
            \Log::info("Cache get user took: " . round((microtime(true) - $cacheStart) * 1000) . "ms");

            if ($cachedUser) {
                Auth::setUser($cachedUser);

                // Load cached roles into Spatie so it skips the DB query
                $rolesStart = microtime(true);
                $roles = Cache::get("auth.user.{$userId}.roles");
                if ($roles) {
                    $cachedUser->setRelation('roles', $roles);
                    $cachedUser->setRelation('permissions', 
                        Cache::get("auth.user.{$userId}.permissions", collect())
                    );
                }
                \Log::info("Cache get roles took: " . round((microtime(true) - $rolesStart) * 1000) . "ms");
            } else {
                $userStart = microtime(true);
                $user = Auth::user();
                \Log::info("Auth::user() took: " . round((microtime(true) - $userStart) * 1000) . "ms");
                
                $putStart = microtime(true);
                Cache::put($cacheKey, $user, now()->addMinutes(30));
                \Log::info("Cache put user took: " . round((microtime(true) - $putStart) * 1000) . "ms");
            }
        }
        
        \Log::info("CacheAuthUser total: " . round((microtime(true) - $start) * 1000) . "ms");

        return $next($request);
    }
}
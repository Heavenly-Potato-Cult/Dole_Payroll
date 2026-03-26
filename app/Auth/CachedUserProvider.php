<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

class CachedUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $cacheKey = "auth.user.{$identifier}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($identifier) {
            return User::find($identifier);
        });
    }
}
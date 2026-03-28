<?php

namespace App\Providers;

use App\Auth\CachedUserProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Models\PayrollBatch;
use App\Policies\PayrollPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::provider('cached-eloquent', function ($app, array $config) {
            return new CachedUserProvider($app['hash'], $config['model']);
        });

        // Log ALL queries with their execution time
        DB::listen(function ($query) {
            Log::info('DB QUERY: ' . round($query->time) . 'ms — ' . $query->sql);
        });

        View::composer('layouts.app', function ($view) {
        if (Auth::check()) {
            $userId = Auth::id();
            $roles = Cache::get("auth.user.{$userId}.roles");
            $userRole = $roles?->first()?->name ?? '';
        } else {
            $userRole = '';
        }
        $view->with('userRole', $userRole);
    });
    }
}
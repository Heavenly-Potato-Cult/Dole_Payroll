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
        $start = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        Log::info('AppServiceProvider boot START: ' . round((microtime(true) - $start) * 1000) . 'ms');
        
        $providerStart = microtime(true);
        Auth::provider('cached-eloquent', function ($app, array $config) {
            return new CachedUserProvider($app['hash'], $config['model']);
        });
        Log::info('Auth provider registration: ' . round((microtime(true) - $providerStart) * 1000) . 'ms');

        $composerStart = microtime(true);
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
        Log::info('View composer registration: ' . round((microtime(true) - $composerStart) * 1000) . 'ms');
        
        Log::info('AppServiceProvider boot END: ' . round((microtime(true) - $start) * 1000) . 'ms');
    }
    
    public function register(): void
    {
        $start = microtime(true);
        Log::info('AppServiceProvider register START: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');
        
        // Add any registration logic here
        
        Log::info('AppServiceProvider register END: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');
    }
}
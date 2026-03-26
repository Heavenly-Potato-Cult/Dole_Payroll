<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TimingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        
        Log::info('MIDDLEWARE START: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');

        $response = $next($request);

        Log::info('AFTER NEXT: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');

        // Force session save NOW and time it
        $start = microtime(true);
        $request->session()->save();
        $elapsed = round((microtime(true) - $start) * 1000);
        Log::info("SESSION SAVE took: {$elapsed}ms");

        Log::info('MIDDLEWARE END: ' . round((microtime(true) - LARAVEL_START) * 1000) . 'ms');

        return $response;
    }
}
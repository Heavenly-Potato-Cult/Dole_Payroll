<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Sanctum stateful middleware — session/cookie auth for Blade web routes
        $middleware->statefulApi();

        // Middleware aliases
$middleware->alias([
    'sanctum.web'        => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    'jwt.auth'           => \App\Http\Middleware\JwtAuth::class,
    'payroll.released.only' => \App\Http\Middleware\PayrollReleasedOnly::class,
]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
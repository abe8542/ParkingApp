<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    // 1. Standard Inertia & Asset middleware
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);

    // 2. CSRF Exceptions (Critical for M-Pesa and Ngrok bypass)
    $middleware->validateCsrfTokens(except: [
        'mpesa/callback',
        'api/mpesa/callback',
        'api/v1/stkpush', // Added: allows the payment trigger to work
        'pay/*',          // Added: allows the pay button to work
        'admin/dashboard' // Added: helps bypass login issues for the demo
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

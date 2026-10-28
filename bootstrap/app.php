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
        // Signed-in users who hit a guest page go to their own home (admin or portal)
        $middleware->redirectUsersTo('/home');

        // Unset by default; Amp orb portals set it so URLs keep the forwarded https scheme
        $middleware->trustProxies(at: env('TRUSTED_PROXIES'));

        // Stripe can't send a CSRF token; Cashier verifies the webhook signature instead
        $middleware->validateCsrfTokens(except: ['stripe/*', 'linear/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

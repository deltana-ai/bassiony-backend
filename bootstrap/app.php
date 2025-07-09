<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SnakeCaseMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
          //  \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
           SnakeCaseMiddleware::class,
           ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'api' => ForceJsonResponse::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'resolve.guard' => \App\Http\Middleware\ResolveSanctumGuard::class,
            'ensure.guard' => \App\Http\Middleware\EnsureGuardMatchesToken::class,


        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

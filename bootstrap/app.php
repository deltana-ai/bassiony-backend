<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SnakeCaseMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Illuminate\Routing\Router;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withRouting(function (Router $router) {
          $router->group([
              'middleware' => 'api',
          ], function () {
              require base_path('routes/api.php');
          });
         $router->group([
             'middleware' => 'web',
         ], function () {
             require base_path('routes/web.php');
         });
         $router->group([
             'middleware' => 'api',
             'prefix' => 'api/owner',
         ], function () {
             require base_path('routes/api/owner.php');
         });

         // Web: Company Panel (React - Session Based)
         $router->group([
             'middleware' => 'api',
             'prefix' => 'api/company',
         ], function () {
             require base_path('routes/api/company.php');
         });

         // API: Client (Mobile - Token Based)
         $router->group([
             'middleware' => 'api',
             'prefix' => 'api/client',
         ], function () {
             require base_path('routes/api/client.php');
         });

         // API: Pharmacy (React + Mobile)
         $router->group([
             'middleware' => 'api',
             'prefix' => 'api/pharmacy',
         ], function () {
             require base_path('routes/api/pharmacy.php');
         });

         // API: Delivery (React + Mobile)
         $router->group([
             'middleware' => 'api',
             'prefix' => 'api/delivery',
         ], function () {
             require base_path('routes/api/delivery.php');
         });
     })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

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

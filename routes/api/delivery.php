<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Delivery\Api\{DriverAuthController,DriverProfileController,DriverContactController};
use App\Http\Controllers\Delivery\Web\DriverAuthController as WebDriverAuthController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

    RateLimiter::for('medications_limiter', function ($request) {
        return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
    });
    Route::middleware(['guest:driver', 'throttle:medications_limiter'])->group(function () {
      Route::post('/register', [DriverAuthController::class, 'driverRegister']);
      Route::post('/login', [DriverAuthController::class, 'driverLogin']);
      Route::post('/web-register', [WebDriverAuthController::class, 'register']);

      Route::post('/web-login', [WebDriverAuthController::class, 'login']);

      Route::get('/verify-email/{id}/{hash}',[WebDriverAuthController::class, 'invokeEmail'])->middleware(['signed'])->name('verification.verify');
    });
    Route::middleware(['auth:driver'])->group(function () {
        Route::post('/web-logout', [WebDriverAuthController::class, 'logout']);
        Route::post('/logout', [DriverAuthController::class, 'driverLogout']);

        Route::get('/profile', [DriverProfileController::class, 'get']);
        Route::put('/profile-update', [DriverProfileController::class, 'update']);
        Route::post('/contact-us', [DriverContactController::class, 'store']);

    });

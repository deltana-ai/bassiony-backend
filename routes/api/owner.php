<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Web\{OwnerAuthController,OwnerProfileController,OwnerContactController};

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

    RateLimiter::for('medications_limiter', function ($request) {
        return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
    });
    Route::middleware(['guest:web-owner', 'throttle:medications_limiter'])->group(function () {

        Route::post('/register', [OwnerAuthController::class, 'register']);
        Route::post('/login', [OwnerAuthController::class, 'login']);
    });
    Route::middleware(['auth:web-owner'])->group(function () {
        Route::post('/logout', [OwnerAuthController::class, 'logout']);
        Route::get('/profile', [OwnerProfileController::class, 'get']);
        Route::put('/profile-update', [OwnerProfileController::class, 'update']);
        Route::post('/contact-us', [OwnerContactController::class, 'store']);

    });

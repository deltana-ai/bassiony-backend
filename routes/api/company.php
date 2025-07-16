<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\Web\CompanyAuthController;
use App\Http\Controllers\Company\Web\ManagerProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

    RateLimiter::for('medications_limiter', function ($request) {
        return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
    });
    Route::middleware(['guest:web-manager', 'throttle:medications_limiter'])->group(function () {

        Route::post('/register', [CompanyAuthController::class, 'register']);
        Route::post('/login', [CompanyAuthController::class, 'login']);
        Route::get('/verify-email/{id}/{hash}',[CompanyAuthController::class, 'invokeEmail'])->middleware(['signed'])->name('verification.verify');
    });
    Route::middleware(['auth:web-manager'])->group(function () {
        Route::post('/logout', [CompanyAuthController::class, 'logout']);
        Route::get('/profile', [ManagerProfileController::class, 'get']);
        Route::put('/profile-update', [ManagerProfileController::class, 'update']);

    });

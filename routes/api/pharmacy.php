<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pharmacy\Api\{PharmacistAuthController,PharmacistProfileController,PharmacistContactController};
use App\Http\Controllers\Pharmacy\Web\PharmacistAuthController as WebPharmacistAuthController;
use Illuminate\Cache\RateLimiting\Limit;

use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('medications_limiter', function ($request) {
    return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
});
  Route::middleware(['guest:pharmacist', 'throttle:medications_limiter'])->group(function () {
    Route::post('/web-register', [WebPharmacistAuthController::class, 'register']);

    Route::post('/web-login', [WebPharmacistAuthController::class, 'login']);

    Route::get('/verify-email/{id}/{hash}',[WebPharmacistAuthController::class, 'invokeEmail'])->middleware(['signed'])->name('verification.verify');

    Route::post('/register', [PharmacistAuthController::class, 'driverRegister']);
    Route::post('/login', [PharmacistAuthController::class, 'driverLogin']);
});
    Route::middleware(['auth:pharmacist'])->group(function () {
        Route::post('/logout', [PharmacistAuthController::class, 'driverLogout']);
        Route::post('/web-logout', [WebPharmacistAuthController::class, 'logout']);

        Route::get('/profile', [PharmacistProfileController::class, 'get']);
        Route::put('/profile-update', [PharmacistProfileController::class, 'update']);
        Route::post('/contact-us', [PharmacistContactController::class, 'store']);

    });

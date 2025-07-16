<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Api\{ClientAuthController,ClientProfileController,ClientContactController};
use App\Http\Controllers\Client\Api\{MedicationController,CartController};

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

    RateLimiter::for('medications_limiter', function ($request) {
        return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
    });
      Route::middleware(['guest:client', 'throttle:medications_limiter'])->group(function () {

      Route::post('/register', [ClientAuthController::class, 'clientRegister']);
      Route::post('/login', [ClientAuthController::class, 'clientLogin']);
    });
    Route::middleware(['auth:client'])->group(function () {
        Route::post('/logout', [ClientAuthController::class, 'clientLogout']);
        Route::get('/profile', [ClientProfileController::class, 'get']);
        Route::put('/profile-update', [ClientProfileController::class, 'update']);
        Route::post('/contact-us', [ClientContactController::class, 'store']);
        Route::post('/add-to-cart', [CartController::class, 'store']);
        Route::get('/cart', [CartController::class, 'index']);
        // Route::store('/order', [CartController::class, 'index']);

    });




    Route::middleware(['auth:client', 'throttle:medications_limiter'])->group(function () {
    Route::post('/medications', [MedicationController::class, 'store']);
    Route::get('/medications/today', [MedicationController::class, 'today']);
    Route::post('/medications/{id}/expire-decision', [MedicationController::class, 'handleExpiration']);
    Route::delete('/medications/{id}', [MedicationController::class, 'destroy']);
    Route::get('/medications/search', [MedicationController::class, 'search']);
});

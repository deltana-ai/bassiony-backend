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
      //***********auth with web ***********************************/
      Route::post('/web-register', [WebDriverAuthController::class, 'register']);
      Route::post('/web-login', [WebDriverAuthController::class, 'login']);
      Route::get('/verify-email/{id}/{hash}',[WebDriverAuthController::class, 'invokeEmail'])->middleware(['signed'])->name('verification.verify.driver');
      Route::post('/web-forgot-password', [WebDriverAuthController::class, 'forgotPassword']);
      Route::post('/web-reset-password', [WebDriverAuthController::class, 'resetPassword']);

      //************auth with mobile***************************************
      Route::post('/register', [DriverAuthController::class, 'driverRegister']);
      Route::post('/login', [DriverAuthController::class, 'driverLogin']);
    });
    Route::middleware(['auth:driver'])->group(function () {
        //***********auth with web ***********************************/
        Route::post('/web-logout', [WebDriverAuthController::class, 'logout']);
        //************auth with mobile***************************************
        Route::post('/logout', [DriverAuthController::class, 'driverLogout']);
        
        //******************common route web and mobile************************************
        Route::get('/profile', [DriverProfileController::class, 'get']);
        Route::put('/profile-update', [DriverProfileController::class, 'update']);
        Route::post('/contact-us', [DriverContactController::class, 'store']);

    });

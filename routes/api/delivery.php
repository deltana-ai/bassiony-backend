<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Delivery\Api\{DriverAuthController,DriverProfileController,DriverContactController};

    Route::post('/register', [DriverAuthController::class, 'driverRegister']);
    Route::post('/login', [DriverAuthController::class, 'driverLogin']);

    Route::middleware(['auth:driver'])->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'driverLogout']);
        Route::get('/profile', [DriverProfileController::class, 'get']);
        Route::put('/profile-update', [DriverProfileController::class, 'update']);
        Route::post('/contact-us', [DriverContactController::class, 'store']);

    });

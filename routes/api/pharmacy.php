<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pharmacy\Api\{PharmacistAuthController,PharmacistProfileController,PharmacistContactController};

    Route::post('/register', [PharmacistAuthController::class, 'driverRegister']);
    Route::post('/login', [PharmacistAuthController::class, 'driverLogin']);

    Route::middleware(['auth:pharmacist'])->group(function () {
        Route::post('/logout', [PharmacistAuthController::class, 'driverLogout']);
        Route::get('/profile', [PharmacistProfileController::class, 'get']);
        Route::put('/profile-update', [PharmacistProfileController::class, 'update']);
        Route::post('/contact-us', [PharmacistContactController::class, 'store']);

    });

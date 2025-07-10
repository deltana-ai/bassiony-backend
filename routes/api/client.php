<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Api\{ClientAuthController,ClientProfileController,ClientContactController};

    Route::post('/register', [ClientAuthController::class, 'clientRegister']);
    Route::post('/login', [ClientAuthController::class, 'clientLogin']);

    Route::middleware(['auth:client'])->group(function () {
        Route::post('/logout', [ClientAuthController::class, 'clientLogout']);
        Route::get('/profile', [ClientProfileController::class, 'get']);
        Route::put('/profile-update', [ClientProfileController::class, 'update']);
        Route::post('/contact-us', [ClientContactController::class, 'store']);

    });

<?php

use Illuminate\Support\Facades\Route;
use Modules\ClientModule\Http\Controllers\ClientModuleController;
use Modules\ClientModule\Http\Controllers\Api\Auth\ClientAuthController;
use Modules\ClientModule\Http\Controllers\Api\{ClientProfileController,ClientContactController};

Route::prefix('client')->group(function () {
    Route::post('/register', [ClientAuthController::class, 'clientRegister']);
    Route::post('/login', [ClientAuthController::class, 'clientLogin']);
    Route::middleware(['auth:client'])->group(function () {
        Route::post('/logout', [ClientAuthController::class, 'clientLogout']);
        Route::get('/profile', [ClientProfileController::class, 'get']);
        Route::put('/profile-update', [ClientProfileController::class, 'update']);
        Route::post('/contact-us', [ClientContactController::class, 'store']);

    });
});
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('clientmodules', ClientModuleController::class)->names('clientmodule');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Owner\Auth\AuthenticatedSessionController;
// use App\Http\Controllers\Owner\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Owner\Auth\NewPasswordController;
use App\Http\Controllers\Owner\Auth\PasswordResetLinkController;
use App\Http\Controllers\Owner\Auth\RegisteredUserController;
// use App\Http\Controllers\Owner\Auth\VerifyEmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


    Route::middleware('guest:web-owner')->group(function () {
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
        Route::post('/reset-password', [NewPasswordController::class, 'store']);
    });

    Route::middleware('auth:web-owner')->group(function () {
        // Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        //     ->middleware(['signed', 'throttle:6,1']);

        // Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        //     ->middleware(['throttle:6,1']);

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

        Route::get('/user', fn () => auth('web-owner')->user());
    });

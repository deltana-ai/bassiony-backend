<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pharmacy\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Pharmacy\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Pharmacy\Auth\NewPasswordController;
use App\Http\Controllers\Pharmacy\Auth\PasswordResetLinkController;
use App\Http\Controllers\Pharmacy\Auth\RegisteredUserController;
 use App\Http\Controllers\Pharmacy\Auth\VerifyEmailController;
 use Illuminate\Foundation\Auth\EmailVerificationRequest;


    Route::middleware('guest:web-pharmacist')->group(function () {
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
        Route::post('/reset-password', [NewPasswordController::class, 'store']);
    });

    Route::middleware('auth:web-pharmacist')->group(function () {
        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class);
        //     ->middleware(['signed', 'throttle:6,1']);
        //
        // Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        //     ->middleware(['throttle:6,1']);
        //
        // Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

        Route::get('/user', fn () => auth('web-pharmacist')->user());
    });

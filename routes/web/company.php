<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Company\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Company\Auth\NewPasswordController;
use App\Http\Controllers\Company\Auth\PasswordResetLinkController;
use App\Http\Controllers\Company\Auth\RegisteredUserController;
use App\Http\Controllers\Company\Auth\VerifyEmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Company\Web\ManagerProfileController;


    Route::middleware('guest:web-manager')->group(function () {

        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
        Route::post('/reset-password', [NewPasswordController::class, 'store']);
    });
    Route::get('/test', function () {

        return view('test');
    });
    Route::middleware(['auth:web-manager'])->group(function () {
        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])->name('company.verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['throttle:6,1'])->name('company.verification.send');

            Route::get('/profile', [ManagerProfileController::class, 'get']);
            Route::put('/profile-update', [ManagerProfileController::class, 'update']);


    });
    Route::middleware(['auth:web-manager','verified'])->group(function () {


        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);


    });

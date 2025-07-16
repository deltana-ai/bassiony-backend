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


    // Route::middleware('guest:web-manager')->group(function () {
    //     Route::get('register', [RegisteredUserController::class, 'create'])
    //         ->name('register');
    //     Route::get('login', [AuthenticatedSessionController::class, 'create'])
    //           ->name('login');
    //     Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //           ->name('password.request');
    //     Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    //           ->name('password.reset');
    //     Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    //     Route::post('/register', [RegisteredUserController::class, 'store']);
    //     Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    //     Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    // });
    Route::get('/test', function () {

        return view('test');
    });
    Route::middleware(['auth:web-manager'])->group(function () {
        // Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        //     ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        //
        // Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        //     ->middleware(['throttle:6,1'])->name('verification.send');
        //
        //     Route::get('/profile', [ManagerProfileController::class, 'get']);
        //     Route::put('/profile-update', [ManagerProfileController::class, 'update']);


    });
    Route::middleware(['auth:web-manager','verified'])->group(function () {


        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);


    });

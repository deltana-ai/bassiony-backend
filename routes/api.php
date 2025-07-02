<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Api\Auth\{ClientAuthController,PharmacistAuthController,DriverAuthController};
use App\Http\Controllers\Api\Profile\{ClientProfileController,PharmacistProfileController,DriverProfileController};
use App\Http\Controllers\Api\Point\UserPointController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});




//////////////////////////////////////// team ////////////////////////////////
// الصيادلة
Route::middleware(['resolve.guard', 'ensure.guard:App\Models\Pharmacist'])->group(function () {
    Route::get('/pharmacist/points/summary', [UserPointController::class, 'getPointsSummary']);
    Route::get('/pharmacist/points/earned', [UserPointController::class, 'getEarnedPoints']);
    Route::get('/pharmacist/points/spent', [UserPointController::class, 'getSpentPoints']);
    Route::get('/pharmacist/points/expired', [UserPointController::class, 'getExpiredPoints']);
});

// العملاء
Route::middleware(['resolve.guard', 'ensure.guard:App\Models\User'])->group(function () {
    Route::get('/client/points/summary', [UserPointController::class, 'getPointsSummary']);
    Route::get('/client/points/earned', [UserPointController::class, 'getEarnedPoints']);
    Route::get('/client/points/spent', [UserPointController::class, 'getSpentPoints']);
    Route::get('/client/points/expired', [UserPointController::class, 'getExpiredPoints']);
});


//////////////////////////////////////// logo company ////////////////////////////////
Route::prefix('client')->group(function () {
    Route::post('/register', [ClientAuthController::class, 'clientRegister']);
    Route::post('/login', [ClientAuthController::class, 'clientLogin']);
    Route::post('/verify-otp', [ClientAuthController::class, 'clientVerify']);
    Route::post('/forgot-password', [ClientAuthController::class, 'clientForgotPassword']);
    Route::post('/reset-password', [ClientAuthController::class, 'clientResetPassword']);

    Route::middleware(['resolve.guard','ensure.guard:App\Models\User'])->group(function () {
        Route::post('/logout', [ClientAuthController::class, 'clientLogout']);
        Route::get('/profile', [ClientProfileController::class, 'get']);
        Route::put('/profile-update', [ClientProfileController::class, 'update']);

    });
});
///////////////////////////////////////////////////////////////////////////
Route::prefix('pharmacist')->group(function () {
    Route::post('/register', [PharmacistAuthController::class, 'pharmacistRegister']);
    Route::post('/login', [PharmacistAuthController::class, 'pharmacistLogin']);
    Route::post('/verify-otp', [PharmacistAuthController::class, 'pharmacistVerify']);
    Route::post('/forgot-password', [PharmacistAuthController::class, 'pharmacistForgotPassword']);
    Route::post('/reset-password', [PharmacistAuthController::class, 'pharmacistResetPassword']);

    Route::middleware(['resolve.guard','ensure.guard:App\Models\Pharmacist'])->group(function () {
        Route::post('/logout', [PharmacistAuthController::class, 'pharmacistLogout']);
        Route::get('/profile', [PharmacistProfileController::class, 'get']);
        Route::put('/profile-update', [PharmacistProfileController::class, 'update']);    });
});
/////////////////////////////////////////////////////////////////////////////////
Route::prefix('driver')->group(function () {
    Route::post('/register', [DriverAuthController::class, 'driverRegister']);
    Route::post('/login', [DriverAuthController::class, 'driverLogin']);
    Route::post('/verify-otp', [DriverAuthController::class, 'driverVerify']);
    Route::post('/forgot-password', [DriverAuthController::class, 'driverForgotPassword']);
    Route::post('/reset-password', [DriverAuthController::class, 'driverResetPassword']);

    Route::middleware(['resolve.guard','ensure.guard:App\Models\Driver'])->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'driverLogout']);
        Route::get('/profile', [DriverProfileController::class, 'get']);
        Route::put('/profile-update', [DriverProfileController::class, 'update']);
    });
});
///////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => []], static function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    Route::get('/get-unused-media', [MediaController::class, 'getUnUsedImages']);
    Route::delete('/delete-unused-media', [MediaController::class, 'deleteUnUsedImages']);
    Route::post('/media/delete', [MediaController::class, 'deleteImagesByIds']);
});
Route::get('/get-media/{media}', [MediaController::class, 'show']);
Route::post('/media-array', [MediaController::class, 'showMedia']);
Route::post('/media-upload-many', [MediaController::class, 'storeMany']);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pharmacy\Api\{PharmacistAuthController,PharmacistProfileController,PharmacistContactController};
use App\Http\Controllers\Pharmacy\Web\PharmacistAuthController as WebPharmacistAuthController;
use Illuminate\Cache\RateLimiting\Limit;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\Common\Point\UserPointController;

use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('medications_limiter', function ($request) {
    return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
});

  Route::middleware(['guest:pharmacist', 'throttle:medications_limiter'])->group(function () {
  //***********auth with web ***********************************/
    Route::post('/web-register', [WebPharmacistAuthController::class, 'register']);
    Route::post('/web-login', [WebPharmacistAuthController::class, 'login']);
    Route::post('/web-forgot-password', [WebPharmacistAuthController::class, 'forgotPassword']);
    Route::post('/web-reset-password', [WebPharmacistAuthController::class, 'resetPassword']);

    Route::get('/verify-email/{id}/{hash}',[WebPharmacistAuthController::class, 'invokeEmail'])->middleware(['signed'])->name('verification.verify.pharmacist');



    Route::post('/register', [PharmacistAuthController::class, 'pharmacistRegister']);
    Route::post('/login', [PharmacistAuthController::class, 'pharmacistLogin']);

});

Route::middleware(['auth:pharmacist'])->group(function () {
    Route::post('/add-favorites', [FavoriteController::class, 'indexUser']);
    Route::get('/get-favorites', [FavoriteController::class, 'addFavorite']);

});



Route::middleware(['auth:pharmacist'])->group(function () {
  //*********************auth with web***************************
    Route::get('/verify-email/{id}/{hash}',[WebPharmacistAuthController::class, 'invokeEmail'])->middleware(['signed'])->name('verification.verify.pharmacist');
    Route::post('/email-resend', [WebPharmacistAuthController::class, 'resentEmail']);
    Route::post('/web-logout', [WebPharmacistAuthController::class, 'logout']);
 //*********************auth mobile*********************************************
    Route::post('/logout', [PharmacistAuthController::class, 'pharmacistLogout']);
 //******************common route web and mobile************************************
    Route::get('/profile', [PharmacistProfileController::class, 'get']);
    Route::put('/profile-update', [PharmacistProfileController::class, 'update']);
    Route::post('/contact-us', [PharmacistContactController::class, 'store']);


});


// الصيادلة
Route::middleware((['guest:pharmacist', 'throttle:medications_limiter']))->group(function () {
    Route::get('/points/summary', [UserPointController::class, 'getPointsSummary']);
    Route::get('/points/earned', [UserPointController::class, 'getEarnedPoints']);
    Route::get('/points/spent', [UserPointController::class, 'getSpentPoints']);
    Route::get('/points/expired', [UserPointController::class, 'getExpiredPoints']);

});



    Route::middleware(['guest:pharmacist'])->group(function () {
        Route::post('/create-offer', [OfferController::class, 'createOffer']);
});

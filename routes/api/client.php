<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\Client\Api\CartController;

use App\Http\Controllers\Client\Api\ClientAuthController;
use App\Http\Controllers\Client\Api\ClientContactController;
use App\Http\Controllers\Client\Api\ClientProfileController;

use App\Http\Controllers\Client\Api\MedicationController;use App\Http\Controllers\Common\Point\UserPointController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProductController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

RateLimiter::for('medications_limiter', function ($request) {
    return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
});
Route::middleware(['guest:client', 'throttle:medications_limiter'])->group(function () {

    Route::post('/register', [ClientAuthController::class, 'clientRegister']);
    Route::post('/login', [ClientAuthController::class, 'clientLogin']);
});
Route::middleware(['auth:client'])->group(function () {
    Route::post('/logout', [ClientAuthController::class, 'clientLogout']);
    Route::get('/profile', [ClientProfileController::class, 'get']);
    Route::put('/profile-update', [ClientProfileController::class, 'update']);
    Route::post('/contact-us', [ClientContactController::class, 'store']);
    Route::apiResource('user-addresses', ClientAddressController::class)->except(['create','show','edit']);
    Route::put('/profile-image/',[ClientProfileController::class,'updateImage'])->name('profile-image.update');
    Route::put('/user-language/',[ClientProfileController::class,'updateLang'])->name('user-lang.update');
    Route::post('/add-to-cart', [CartController::class, 'store']);
    Route::get('/cart', [CartController::class, 'index']);
    // Route::store('/order', [CartController::class, 'index']);

});

Route::middleware(['auth:client'])->group(function () {
    Route::post('/medications', [MedicationController::class, 'store']);
    Route::get('/medications/today', [MedicationController::class, 'today']);
    Route::post('/medications/{id}/expire-decision', [MedicationController::class, 'handleExpiration']);
    Route::delete('/medications/{id}', [MedicationController::class, 'destroy']);
    Route::get('/medications/search', [MedicationController::class, 'search']);
});

// العملاء
Route::middleware(['auth:client'])->group(function () {
    Route::get('/points/summary', [UserPointController::class, 'getPointsSummary']);
    Route::get('/points/earned', [UserPointController::class, 'getEarnedPoints']);
    Route::get('/points/spent', [UserPointController::class, 'getSpentPoints']);
    Route::get('/points/expired', [UserPointController::class, 'getExpiredPoints']);
});






//////////////////////////////////////////category//////////////////////////////////////////

Route::group(['middleware' => []], static fn(): array=> [
    Route::post('/category/index', [CategoryController::class, 'index']),
    Route::post('/category/restore', [CategoryController::class, 'restore']),
    Route::delete('/category/delete', [CategoryController::class, 'destroy']),
    Route::put('/category/{id}/{column}', [CategoryController::class, 'toggle']),
    Route::apiResource('category', CategoryController::class),
]);
Route::get('/fetch-category', [CategoryController::class, 'fetchCategory']);

//////////////////////////////////////////category//////////////////////////////////////////







Route::get('/fetch-brand', [BrandController::class, 'fetchBrand']);
Route::get('/fetch-product', [ProductController::class, 'fetchProduct']);
Route::get('/fetch-pharmacy', [PharmacyController::class, 'fetchPharmacy']);
Route::get('/offer-products', [OfferController::class, 'getOfferProducts']);


Route::middleware(['auth:client'])->group(function () {
    Route::post('/add-favorites', [FavoriteController::class, 'indexUser']);
    Route::get('/get-favorites', [FavoriteController::class, 'addFavorite']);

});

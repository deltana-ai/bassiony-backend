<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Api\Auth\{ClientAuthController, PharmacistAuthController, DriverAuthController};
use App\Http\Controllers\Api\Profile\{ClientProfileController, PharmacistProfileController, DriverProfileController};
use App\Http\Controllers\BrandController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



//////////////////////////////////////// category ////////////////////////////////

Route::middleware([])->group(function () {
    Route::post('/category/index', [CategoryController::class, 'index']);
    Route::post('category/restore', [CategoryController::class, 'restore']);
    Route::delete('category/delete', [CategoryController::class, 'destroy']);
    Route::delete('category/force-delete', [CategoryController::class, 'forceDelete']);
    Route::put('/category/{id}/{column}', [CategoryController::class, 'toggle']);
    Route::apiResource('category', CategoryController::class);
    Route::get('fetch-category', [CategoryController::class, 'fetchCategory']);
});

//////////////////////////////////////// category ////////////////////////////////


//////////////////////////////////////// brand ////////////////////////////////

Route::middleware([])->group(function () {
    Route::post('/brand/index', [BrandController::class, 'index']);
    Route::post('brand/restore', [BrandController::class, 'restore']);
    Route::delete('brand/delete', [BrandController::class, 'destroy']);
    Route::delete('brand/force-delete', [BrandController::class, 'forceDelete']);
    Route::put('/brand/{id}/{column}', [BrandController::class, 'toggle']);
    Route::apiResource('brand', BrandController::class);
    Route::get('fetch-brand', [BrandController::class, 'fetchBrand']);
});

//////////////////////////////////////// brand ////////////////////////////////



//////////////////////////////////////// product ////////////////////////////////

Route::middleware([])->group(function () {
    Route::post('/product/index', [ProductController::class, 'index']);
    Route::post('product/restore', [ProductController::class, 'restore']);
    Route::delete('product/delete', [ProductController::class, 'destroy']);
    Route::delete('product/force-delete', [ProductController::class, 'forceDelete']);
    Route::put('/product/{id}/{column}', [ProductController::class, 'toggle']);
    Route::apiResource('product', ProductController::class);
    Route::get('fetch-product', [ProductController::class, 'fetchProduct']);
});

//////////////////////////////////////// product ////////////////////////////////



//////////////////////////////////////// pharmacy ////////////////////////////////

Route::middleware([])->group(function () {
    Route::post('/pharmacy/index', [PharmacyController::class, 'index']);
    Route::post('pharmacy/restore', [PharmacyController::class, 'restore']);
    Route::delete('pharmacy/delete', [PharmacyController::class, 'destroy']);
    Route::delete('pharmacy/force-delete', [PharmacyController::class, 'forceDelete']);
    Route::put('/pharmacy/{id}/{column}', [PharmacyController::class, 'toggle']);
    Route::apiResource('pharmacy', PharmacyController::class);
    Route::get('fetch-pharmacy', [PharmacyController::class, 'fetchPharmacy']);
});

//////////////////////////////////////// pharmacy ////////////////////////////////

//////////////////////////////////////// offer ////////////////////////////////

Route::middleware([])->group(function () {
    Route::post('/offer/index', [OfferController::class, 'index']);
    Route::post('offer/restore', [OfferController::class, 'restore']);
    Route::delete('offer/delete', [OfferController::class, 'destroy']);
    Route::delete('offer/force-delete', [OfferController::class, 'forceDelete']);
    Route::put('/offer/{id}/{column}', [OfferController::class, 'toggle']);
    Route::apiResource('offer', OfferController::class);
    Route::get('/offer-products', [OfferController::class, 'getOfferProducts']);
});


//////////////////////////////////////// offer ////////////////////////////////



Route::middleware(['resolve.guard', 'ensure.guard:App\Models\User'])->group(function () {
    Route::post('/add-favorites', [FavoriteController::class, 'store']);
    Route::get('/get-favorites', [FavoriteController::class, 'index']);
});










//////////////////////////////////////// logo company ////////////////////////////////
Route::prefix('client')->group(function () {
    Route::post('/register', [ClientAuthController::class, 'clientRegister']);
    Route::post('/login', [ClientAuthController::class, 'clientLogin']);
    Route::post('/verify-otp', [ClientAuthController::class, 'clientVerify']);
    Route::post('/forgot-password', [ClientAuthController::class, 'clientForgotPassword']);
    Route::post('/reset-password', [ClientAuthController::class, 'clientResetPassword']);

    Route::middleware(['resolve.guard', 'ensure.guard:App\Models\User'])->group(function () {
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

    Route::middleware(['resolve.guard', 'ensure.guard:App\Models\Pharmacist'])->group(function () {
        Route::post('/logout', [PharmacistAuthController::class, 'pharmacistLogout']);
        Route::get('/profile', [PharmacistProfileController::class, 'get']);
        Route::put('/profile-update', [PharmacistProfileController::class, 'update']);
    });
});
/////////////////////////////////////////////////////////////////////////////////
Route::prefix('driver')->group(function () {
    Route::post('/register', [DriverAuthController::class, 'driverRegister']);
    Route::post('/login', [DriverAuthController::class, 'driverLogin']);
    Route::post('/verify-otp', [DriverAuthController::class, 'driverVerify']);
    Route::post('/forgot-password', [DriverAuthController::class, 'driverForgotPassword']);
    Route::post('/reset-password', [DriverAuthController::class, 'driverResetPassword']);

    Route::middleware(['resolve.guard', 'ensure.guard:App\Models\Driver'])->group(function () {
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

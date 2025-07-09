<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\Auth\{PharmacistAuthController,DriverAuthController};
use App\Http\Controllers\Api\Profile\{PharmacistProfileController,DriverProfileController};
use App\Http\Controllers\Api\Contact\{PharmacistContactController,DriverContactController};
use App\Http\Controllers\Api\Point\UserPointController;
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

///////////////////////////////////////////////////////////////////////////
Route::prefix('pharmacist')->group(function () {
    Route::post('/register', [PharmacistAuthController::class, 'pharmacistRegister']);
    Route::post('/login', [PharmacistAuthController::class, 'pharmacistLogin']);
    // Route::post('/verify-otp', [PharmacistAuthController::class, 'pharmacistVerify']);
    // Route::post('/forgot-password', [PharmacistAuthController::class, 'pharmacistForgotPassword']);
    // Route::post('/reset-password', [PharmacistAuthController::class, 'pharmacistResetPassword']);

    Route::middleware(['auth:pharmacist'])->group(function () {
        Route::post('/logout', [PharmacistAuthController::class, 'pharmacistLogout']);
        Route::get('/profile', [PharmacistProfileController::class, 'get']);
        Route::put('/profile-update', [PharmacistProfileController::class, 'update']);
        Route::post('/contact-us', [PharmacistContactController::class, 'store']);
    });
});
/////////////////////////////////////////////////////////////////////////////////
Route::prefix('driver')->group(function () {
    Route::post('/register', [DriverAuthController::class, 'driverRegister']);
    Route::post('/login', [DriverAuthController::class, 'driverLogin']);
    // Route::post('/verify-otp', [DriverAuthController::class, 'driverVerify']);
    // Route::post('/forgot-password', [DriverAuthController::class, 'driverForgotPassword']);
    // Route::post('/reset-password', [DriverAuthController::class, 'driverResetPassword']);

    Route::middleware(['auth:driver'])->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'driverLogout']);
        Route::get('/profile', [DriverProfileController::class, 'get']);
        Route::put('/profile-update', [DriverProfileController::class, 'update']);
        Route::post('/contact-us', [DriverContactController::class, 'store']);

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

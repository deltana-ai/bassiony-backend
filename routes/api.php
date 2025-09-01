<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\{AddressController, BrandController, CartController, CategoryController, ProductController,OfferController,FavoriteController, OrderController, PharmacistController, RateController,PillReminderController};
use App\Http\Controllers\Dashboard\BrandController as AdminBrandController;
use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PharmacyProductController;
use App\Http\Controllers\PharmacyRatingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////// User ////////////////////////////////

Route::middleware(['auth:admins'])->group(function () {
    Route::post('/user/index', [UserController::class, 'index']);
    Route::post('user/restore', [UserController::class, 'restore']);
    Route::delete('user/delete', [UserController::class, 'destroy']);
    Route::put('/user/{id}/{column}', [UserController::class, 'toggle']);
    Route::delete('user/force-delete', [UserController::class, 'forceDelete']);
    Route::apiResource('user', UserController::class);
});

Route::prefix('user')->middleware('throttle:20')->group(function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('change-password', [UserController::class, 'changePassword']);
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:users');
});


//////////////////////////////////////// User ////////////////////////////////

//////////////////////////////////////// Pharmacist ////////////////////////////////

Route::middleware(['auth:admins'])->group(function () {
    Route::post('/pharmacist/index', [PharmacistController::class, 'index']);
    Route::post('pharmacist/restore', [PharmacistController::class, 'restore']);
    Route::delete('pharmacist/delete', [PharmacistController::class, 'destroy']);
    Route::put('/pharmacist/{id}/{column}', [PharmacistController::class, 'toggle']);
    Route::delete('pharmacist/force-delete', [PharmacistController::class, 'forceDelete']);
    Route::apiResource('pharmacist', PharmacistController::class);
});

Route::prefix('pharmacist')->middleware('throttle:20')->group(function () {
    Route::post('register', [PharmacistController::class, 'register']);
    Route::post('login', [PharmacistController::class, 'login']);
    Route::post('change-password', [PharmacistController::class, 'changePassword']);
});

Route::prefix('pharmacist')->group(function () {
    Route::post('logout', [PharmacistController::class, 'logout'])
        ->middleware('auth:pharmacists');
});


//////////////////////////////////////// Pharmacist ////////////////////////////////








//////////////////////////////////pharmacies  /////////////////////////////////////////

Route::get('pharmacies/get', [PharmacyController::class, 'index']);
Route::get('pharmacies/show/{id}', [PharmacyController::class,'show']);
Route::get('pharmacies/{id}/products', [PharmacyController::class,'getPharmacyProducts']);
Route::get('pharmacies/{id}/offers', [PharmacyController::class,'getPharmacyOffers']);
Route::get('pharmacies/{id}/categories', [PharmacyController::class,'getPharmacyCategories']);
Route::get('pharmacies/{id}/brands', [PharmacyController::class,'getPharmacyBrands']);

//////////////////////////////////////pharmacies///////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////// Brands /////////////////////////////////////////

Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {

    Route::post('brands/index', [AdminBrandController::class, 'index']);
    Route::put('/brands/{id}/{column}', [AdminBrandController::class, 'toggle']);
    Route::delete('brands/delete', [AdminBrandController::class, 'destroy']);
    Route::post('brands/restore', [AdminBrandController::class, 'restore']);
    Route::delete('brands/force-delete', [AdminBrandController::class, 'forceDelete']);
    Route::apiResource('brands', AdminBrandController::class)->except(['destroy','index']);
});
Route::get('brands/get', [BrandController::class, 'index']);
Route::get('brands/show/{id}', [BrandController::class, 'show']);

/////////////////////////////// Brands /////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////categories //////////////////////////////////////


Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {

    Route::post('categories/index', [AdminCategoryController::class, 'index']);
    Route::put('/categories/{id}/{column}', [AdminCategoryController::class, 'toggle']);
    Route::delete('categories/delete', [AdminCategoryController::class, 'destroy']);
    Route::post('categories/restore', [AdminCategoryController::class, 'restore']);
    Route::delete('categories/force-delete', [AdminCategoryController::class, 'forceDelete']);
    Route::apiResource('categories', AdminCategoryController::class)->except(['destroy','index']);

});

Route::get('categories/get', [CategoryController::class, 'index']);
Route::get('categories/show/{id}', [CategoryController::class, 'show']);


///////////////////////////////////////categories //////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


Route::middleware(['auth:pharmacists' , 'auth:admins'])->group(function () {

    Route::post('/offer/index', [OfferController::class, 'index']);
    Route::post('/offer/restore', [OfferController::class, 'restore']);
    Route::delete('/offer/delete', [OfferController::class, 'destroy']);
    Route::put('/offer/{id}/{column}', [OfferController::class, 'toggle']);
    Route::delete('/offer/force-delete', [OfferController::class, 'forceDelete']);
    Route::apiResource('offer', OfferController::class);

    Route::post('/offer/{offer}/products/add', [OfferController::class, 'addProductToOffer']);
    Route::post('/offer/{offer}/products/remove', [OfferController::class, 'removeProductFromOffer']);

});
Route::get('/offers/public', [OfferController::class, 'indexPublic']);



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////





////////////////////////////////////////// Admin ////////////////////////////////
Route::middleware(['auth:admins'])->group(function () {
    Route::post('/admin/index', [AdminController::class, 'index']);
    Route::post('admin/restore', [AdminController::class, 'restore']);
    Route::delete('admin/delete', [AdminController::class, 'destroy']);
    Route::delete('admin/force-delete', [AdminController::class, 'forceDelete']);
    Route::put('/admin/{id}/{column}', [AdminController::class, 'toggle']);
    Route::post('/admin-select', [AdminController::class, 'index']);
    Route::post('/admin-logout', [AdminController::class, 'logout']);
    Route::get('/get-admin', [AdminController::class, 'getCurrentAdmin']);
    Route::apiResource('admin', AdminController::class);
});
// في api.php
Route::post('/status-check', [AdminController::class, 'ping']);
Route::post('/admin/login', [AdminController::class, 'login']);
////////////////////////////////////////// Admin ////////////////////////////////




//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




////////////////////////////////////////// media ////////////////////////////////

Route::group(['middleware' => ['api']], static function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    Route::get('/get-unused-media', [MediaController::class, 'getUnUsedImages']);
    Route::delete('/delete-unused-media', [MediaController::class, 'deleteUnUsedImages']);
});
Route::get('/get-media/{media}', [MediaController::class, 'show']);
Route::post('/media-array', [MediaController::class, 'showMedia']);
Route::post('/media-upload-many', [MediaController::class, 'storeMany']);
////////////////////////////////////////// media ////////////////////////////////



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////






//////////////////////////////////////// ContactUs ////////////////////////////////
Route::middleware(['auth:admins'])->group(function () {
    Route::post('/contactus/index', [ContactUsController::class, 'index']);
    Route::post('contactus/restore', [ContactUsController::class, 'restore']);
    Route::delete('contactus/delete', [ContactUsController::class, 'destroy']);
    Route::delete('contactus/force-delete', [ContactUsController::class, 'forceDelete']);
    Route::put('/contactus/{id}/{column}', [ContactUsController::class, 'toggle']);
    Route::apiResource('contactus', ContactUsController::class);
});
Route::post('contact-us-public', [ContactUsController::class, 'store']);
Route::post('publicsss', [ContactUsController::class, 'aaaa']);

//////////////////////////////////////// ContactUs ////////////////////////////////




//////////////////////////////////////// Slider ////////////////////////////////

Route::middleware(['auth:admins'])->group(function () {
    Route::post('slider/index', [SliderController::class, 'index']);
    Route::post('slider/restore', [SliderController::class, 'restore']);
    Route::delete('slider/delete', [SliderController::class, 'destroy']);
    Route::put('/slider/{id}/{column}', [SliderController::class, 'toggle']);
    Route::delete('slider/force-delete', [SliderController::class, 'forceDelete']);
    Route::apiResource('slider', SliderController::class);
});

Route::get('/get-slider', [SliderController::class, 'indexPublic']);

//////////////////////////////////////// Slider ////////////////////////////////



//////////////////////////////////////// products /////////////////////////////////

    Route::middleware(['auth:admins'])->group(function () {
        Route::post('product/index', [ProductController::class, 'index']);
        Route::post('product/restore', [ProductController::class, 'restore']);
        Route::delete('product/delete', [ProductController::class, 'destroy']);
        Route::put('/product/{id}/{column}', [ProductController::class, 'toggle']);
        Route::delete('product/force-delete', [ProductController::class, 'forceDelete']);
        Route::apiResource('product', ProductController::class);
    });

    Route::get('/get-product', [CategoryController::class, 'indexProduct']);
        Route::apiResource('products', ProductController::class);

/////////////////////////////////////// products /////////////////////////////////


// /////////////////////////////////////// rateing /////////////////////////////////////////

Route::middleware(['auth:users'])->group(function () {
    Route::post('rateing/index', [RateController::class, 'index']);
    Route::post('rateing/restore', [RateController::class, 'restore']);
    Route::delete('rateing/delete', [RateController::class, 'destroy']);
    Route::put('/rateing/{id}/{column}', [RateController::class, 'toggle']);
    Route::delete('rateing/force-delete', [RateController::class, 'forceDelete']);
    Route::apiResource('rateing', RateController::class);
});


Route::get('/get-rateing', [RateController::class, 'indexPublic']);


// /////////////////////////////////////// rateing /////////////////////////////////////////



///////////////////////////////// pill reminders /////////////////////////////////


Route::middleware(['auth:users'])->group(function () {
    Route::get('/pill-reminders', [PillReminderController::class, 'index']);
    Route::post('/pill-reminders', [PillReminderController::class, 'store']);
    Route::get('/pill-reminders/{id}', [PillReminderController::class, 'show']);
    Route::put('/pill-reminders/{id}', [PillReminderController::class, 'update']);
    Route::delete('/pill-reminders/{id}', [PillReminderController::class, 'destroy']);
    Route::get('/pill-reminders/schedule', [PillReminderController::class, 'schedule']);
});


//////////////////////////////////////// pill reminders ///////////////////////////////////






////////////////////////////////  pharmacy rate //////////////////////////////

Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {
    Route::post('pharmacy/rate/index', [PharmacyRatingController::class, 'index']);
    Route::get('pharmacy/rate/{id}', [PharmacyRatingController::class, 'show']);
    Route::delete('pharmacy/rate/delete', [PharmacyRatingController::class, 'destroy']);
});

Route::get('pharmacy/{id}/get-rate', [PharmacyRatingController::class, 'indexPublic']);


Route::middleware(['auth:users'])->group(function () {
    Route::apiResource('pharmacy/rate', PharmacyRatingController::class)->except(['index','destroy']);
});
////////////////////////////////pharmacy rate///////////////////////////////////////


















Route::middleware('auth:sanctum')->group(function () {
    // Cart
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('cart/{cartItem}', [CartController::class, 'destroy']);

    // Orders
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/addresses-many', [AddressController::class, 'destroyMany']);
    Route::apiResource('addresses', AddressController::class);
});

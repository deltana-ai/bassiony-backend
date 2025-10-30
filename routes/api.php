<?php

use App\Http\Controllers\{AddressController, BrandController, CardController, CartController, CategoryController, ProductController,OfferController,FavoriteController, OrderController, PharmacistController, RateController,PillReminderController};

use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\Dashboard\{BranchController,BranchProductController, CompanyController, WarehouseController};
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\PharmacyRatingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::post('contact-us-public', [ContactUsController::class, 'store']);
Route::post('publicsss', [ContactUsController::class, 'aaaa']);
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
Route::post('/check-phone', [UserController::class, 'checkPhone']);


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


////////////////////////////////////////brands//////////////////////////////////////////////////////////////////////////////
Route::get('brands/get', [BrandController::class, 'index']);
Route::get('brands/show/{id}', [BrandController::class, 'show']);
/////////////////////////////// Brands /////////////////////////////////////////





//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////categories //////////////////////////////////////
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









//////////////////////////////////////// Slider ////////////////////////////////

Route::get('/get-slider', [SliderController::class, 'indexPublic']);

//////////////////////////////////////// Slider ////////////////////////////////








//////////////////////////////////////// products /////////////////////////////////

   

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



Route::get('pharmacy/{id}/get-rate', [PharmacyRatingController::class, 'indexPublic']);


Route::middleware(['auth:users'])->group(function () {
    Route::apiResource('pharmacy/rate', PharmacyRatingController::class)->except(['index','destroy']);
});
////////////////////////////////pharmacy rate///////////////////////////////////////





////////////////////////////////  favorites  //////////////////////////////


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('favorites', FavoriteController::class)->only(['index','store','destroy']);
});




////////////////////////////////   favorites //////////////////////////////






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
        Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus']);

});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/addresses-many', [AddressController::class, 'destroyMany']);
    Route::apiResource('addresses', AddressController::class);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cards-list', [CardController::class, 'index']);
    Route::post('/cards', [CardController::class, 'store']);
});

/////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////

Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {


    Route::post('branches/index', [BranchController::class, 'index']);
    Route::post('branches/restore', [BranchController::class, 'restore']);
    Route::delete('branches/delete', [BranchController::class, 'destroy']);
    Route::put('/branches/{id}/{column}', [BranchController::class, 'toggle']);
    Route::delete('branches/force-delete', [BranchController::class, 'forceDelete']);
    Route::apiResource('branches', BranchController::class);


});





////////////////////////////////////////////////////////////////////////







require __DIR__.'/admin.php';
require __DIR__.'/company.php';
require __DIR__.'/pharmacy.php';

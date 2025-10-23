<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\{CompanyController, LocationController};
use App\Http\Controllers\Dashboard\BrandController as AdminBrandController;
use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;
use App\Http\Controllers\Dashboard\PharmacyController as AdminPharmacyController ;
use App\Http\Controllers\{AdminController,ProductController,ContactUsController,SliderController,PharmacyRatingController};







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
    Route::apiResource('admin', AdminController::class)->except(['destroy','index']);
});
// في api.php
Route::post('/status-check', [AdminController::class, 'ping']);
Route::post('/admin/login', [AdminController::class, 'login']);
////////////////////////////////////////// Admin ////////////////////////////////



/////////////////////////////// Brands /////////////////////////////////////////
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {

    Route::post('brands/index', [AdminBrandController::class, 'index']);
    Route::put('/brands/{id}/{column}', [AdminBrandController::class, 'toggle']);
    Route::delete('brands/delete', [AdminBrandController::class, 'destroy']);
    Route::post('brands/restore', [AdminBrandController::class, 'restore']);
    Route::delete('brands/force-delete', [AdminBrandController::class, 'forceDelete']);
    Route::apiResource('brands', AdminBrandController::class)->except(['destroy','index']);
});
/////////////////////////////// Brands /////////////////////////////////////////






/////////////////////////////// categories ////////////////////////////////////////////
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {

    Route::post('categories/index', [AdminCategoryController::class, 'index']);
    Route::put('/categories/{id}/{column}', [AdminCategoryController::class, 'toggle']);
    Route::delete('categories/delete', [AdminCategoryController::class, 'destroy']);
    Route::post('categories/restore', [AdminCategoryController::class, 'restore']);
    Route::delete('categories/force-delete', [AdminCategoryController::class, 'forceDelete']);
    Route::apiResource('categories', AdminCategoryController::class)->except(['destroy','index']);

});
/////////////////////////////// categories ////////////////////////////////////////////





//////////////////////////////////////// ContactUs ////////////////////////////////
Route::middleware(['auth:admins'])->group(function () {
    Route::post('/contactus/index', [ContactUsController::class, 'index']);
    Route::post('contactus/restore', [ContactUsController::class, 'restore']);
    Route::delete('contactus/delete', [ContactUsController::class, 'destroy']);
    Route::delete('contactus/force-delete', [ContactUsController::class, 'forceDelete']);
    Route::put('/contactus/{id}/{column}', [ContactUsController::class, 'toggle']);
    Route::apiResource('contactus', ContactUsController::class)->except(['destroy','index','store']);
});

///////////////////////////////////// ContactUs //////////////////////////////////////////





//////////////////////////////////////// Slider ////////////////////////////////
Route::middleware(['auth:admins'])->group(function () {
    Route::post('slider/index', [SliderController::class, 'index']);
    Route::post('slider/restore', [SliderController::class, 'restore']);
    Route::delete('slider/delete', [SliderController::class, 'destroy']);
    Route::put('/slider/{id}/{column}', [SliderController::class, 'toggle']);
    Route::delete('slider/force-delete', [SliderController::class, 'forceDelete']);
    Route::apiResource('slider', SliderController::class)->except(['destroy','index']);
});
///////////////////////////////////////Slider //////////////////////////////////////////////////////////




///////////////////////////////////////pharmacy -rate ///////////////////////////////////////
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {
    Route::post('pharmacy/rate/index', [PharmacyRatingController::class, 'index']);
    Route::get('pharmacy/rate/{id}', [PharmacyRatingController::class, 'show']);
    Route::delete('pharmacy/rate/delete', [PharmacyRatingController::class, 'destroy']);
});
///////////////////////////////////////pharmacy -rate ///////////////////////////////////////







///////////////////////////////////////pharmacies ///////////////////////////////////////
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {
    Route::post('pharmacies/index', [AdminPharmacyController::class, 'index']);
    Route::post('pharmacies/restore', [AdminPharmacyController::class, 'restore']);
    Route::delete('pharmacies/delete', [AdminPharmacyController::class, 'destroy']);
    Route::put('/pharmacies/{id}/{column}', [AdminPharmacyController::class, 'toggle']);
    Route::delete('pharmacies/force-delete', [AdminPharmacyController::class, 'forceDelete']);
    Route::apiResource('pharmacies', AdminPharmacyController::class)->except(['destroy','index']);
});
///////////////////////////////////////pharmacies ///////////////////////////////////////





////////////////////////////////////product////////////////////////////////////////////////////////////////
Route::middleware(['auth:admins'])->group(function () {
    Route::post('product/index', [ProductController::class, 'index']);
    Route::post('product/restore', [ProductController::class, 'restore']);
    Route::delete('product/delete', [ProductController::class, 'destroy']);
    Route::put('/product/{id}/{column}', [ProductController::class, 'toggle']);
    Route::delete('product/force-delete', [ProductController::class, 'forceDelete']);
    Route::apiResource('product', ProductController::class)->except(['destroy','index']);
});
//////////////////////////////////product///////////////////////////////////////////////





//////////////////////////////companies//////////////////////////////////////////////
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {
    Route::post('companies/index', [CompanyController::class, 'index']);
    Route::post('companies/restore', [CompanyController::class, 'restore']);
    Route::delete('companies/delete', [CompanyController::class, 'destroy']);
    Route::put('/companies/{id}/{column}', [CompanyController::class, 'toggle']);
    Route::delete('companies/force-delete', [CompanyController::class, 'forceDelete']);
    Route::apiResource('companies', CompanyController::class)->except(['destroy','index']);
});
////////////////////////////////////////////////////////////////////////////////////////////




///////////////////////////////locations//////////////////////////////////////////////
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {
    Route::post('locations/index', [LocationController::class, 'index']);
    Route::delete('locations/delete', [LocationController::class, 'destroy']);
    Route::apiResource('locations', LocationController::class)->except(['destroy','index']);
});
///////////////////////////////locations//////////////////////////////////////////////

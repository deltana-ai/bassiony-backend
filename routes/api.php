<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\{BrandController,CategoryController};
use App\Http\Controllers\Dashboard\BrandController as AdminBrandController;
use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;

use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

///////////////////////////////added by zeinab /////////////////////////////////////////
//********************************** dashboard brands **************************
Route::middleware(['auth:admins'])->prefix('dashboard')->group(function () {

  Route::apiResource('brands', AdminBrandController::class)->except(['destroy']);
  Route::delete('brands/delete', [AdminBrandController::class, 'destroy']);
  Route::post('brands/restore', [AdminBrandController::class, 'restore']);
  Route::delete('brands/force-delete', [AdminBrandController::class, 'forceDelete']);
           /********************category********************/
  Route::apiResource('categories', AdminCategoryController::class)->except(['destroy']);
  Route::delete('categories/delete', [AdminCategoryController::class, 'destroy']);
  Route::post('categories/restore', [AdminCategoryController::class, 'restore']);
  Route::delete('categories/force-delete', [AdminCategoryController::class, 'forceDelete']);

});
//**************************application brands*************************************
Route::get('brands/get', [BrandController::class, 'index']);
Route::get('brands/show/{id}', [BrandController::class, 'show']);
/***********************application categories ***********************************/
Route::get('categories/get', [CategoryController::class, 'index']);
Route::get('categories/show/{id}', [CategoryController::class, 'show']);
///////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////// user ////////////////////////////////

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


//////////////////////////////////////// user ////////////////////////////////




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






//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////





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



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

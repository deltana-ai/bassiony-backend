<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\{ CompanyController, CompanyOfferController, CompanyOrderController, EmployeeProfileController, EmployeeRoleController, LocationController, PharmacyOrderController, ResponseOfferController, WarehouseController, WarehouseProductController};
use App\Http\Controllers\Dashboard\EmployeeController;
use App\Http\Controllers\ProductController;

Route::middleware(['auth:employees','employee.role:manager'])->prefix('company/dashboard')->name('company.')->group(function () {



    /////////////////////////////////////// warehouses //////////////////////////////////////////////


    Route::post('warehouses/index', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('warehouses/restore', [WarehouseController::class, 'restore']);
    Route::delete('warehouses/delete', [WarehouseController::class, 'destroy']);
    Route::put('/warehouses/{id}/{column}', [WarehouseController::class, 'toggle']);
    Route::delete('warehouses/force-delete', [WarehouseController::class, 'forceDelete']);
    Route::apiResource('warehouses', WarehouseController::class)->except(['index']);
    ///////////////////////////////////////////////////////////////////////////////////////////////



    //////////////////////////////////////locations/////////////////////////////////////////////////
    Route::post('locations/index', [LocationController::class, 'index']);
    Route::apiResource('locations', LocationController::class)->only(['show']);
    ///////////////////////////////////////////////////////////////////////////////////////////////



    ///////////////////////////////////update company data/////////////////////////////////////////
    Route::patch('/our-company', [CompanyController::class, 'update']);
    Route::get('/our-company', [CompanyController::class, 'show']);
    // Route::apiResource('our-company', CompanyController::class)->only(['show','update']);
    //////////////////////////////////////////////////////////////////////////////////////////////



    ////////////////////////////////// roles //////////////////////////////////////////////////////
    Route::post('roles/index', [EmployeeRoleController::class, 'index'])->name('roles.index');
    Route::post('roles/restore', [EmployeeRoleController::class, 'restore']);
    Route::delete('roles/delete', [EmployeeRoleController::class, 'destroy']);
    Route::delete('roles/force-delete', [EmployeeRoleController::class, 'forceDelete']);
    Route::apiResource('roles', EmployeeRoleController::class)->except(['index']);
    //////////////////////////////////////////////////////////////////////////////////////////////



    ////////////////////////////////// employee crud //////////////////////////////////////////////////////
    Route::post('employees/index', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('employees/restore', [EmployeeController::class, 'restore']);
    Route::delete('employees/delete', [EmployeeController::class, 'destroy']);
    Route::put('/employees/{id}/{column}', [EmployeeController::class, 'toggle']);
    Route::delete('employees/force-delete', [EmployeeController::class, 'forceDelete']);
    Route::put('/employees/assign-warehouse', [EmployeeController::class, 'assignWarehouse']);
    Route::put('/employees/assign-role', [EmployeeController::class, 'assignRole']);
    Route::apiResource('employees', EmployeeController::class)->except(['index']);


    //////////////////////////////////////////////////////////////////////////////////////////////



    ////////////////////////////////// warehouse product crud //////////////////////////////////////////////////////
    Route::post('warehouses/{warehouse}/products/index', [WarehouseProductController::class, 'index'])->name('warehouse.products.index');
    Route::delete('warehouses/{warehouse}/products/delete', [WarehouseProductController::class, 'destroy']);
    Route::apiResource('warehouses/{warehouse}/products', WarehouseProductController::class);
    //////////////////////////////////////////////////////////////////////////////////////////////






    /////////////////////////////////show product details//////////////////////////////////////////////////
    Route::post('product/index', [ProductController::class, 'index']);
    Route::get('product/{product}', [ProductController::class,"show"]);
    /////////////////////////////////////////////////////////////////////////////////////////////////////





    //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('offers/index', [CompanyOfferController::class, 'index']);
    Route::delete('offers/delete', [CompanyOfferController::class, 'destroy']);
    Route::put('/offers/{id}/{column}', [CompanyOfferController::class, 'toggle']);
    Route::apiResource('offers', CompanyOfferController::class)->except(['destroy','index']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////



    //////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('response-offers/index', [ResponseOfferController::class, 'index']);
    Route::delete('response-offers/delete', [ResponseOfferController::class, 'destroy']);
    Route::put('/response-offers/{id}/{column}', [ResponseOfferController::class, 'toggle']);
    Route::put('response-offers/update/{responseOffer}', [ResponseOfferController::class, 'updateStatus']);
    Route::put('response-offers/show/{responseOffer}', [ResponseOfferController::class, 'show']);
    /////////////////////////////////////////////////////////////////////////////////////////////////




    ////////////////////////profile employee///////////////////////////////////////////////////////////////
    Route::post('logout', [EmployeeProfileController::class, 'logout']);
    Route::get('show-profile', [EmployeeProfileController::class, 'show']);
    Route::put('/update-profile',[EmployeeProfileController::class,'updateProfile'])->name('profile.update');
    Route::put('update-password', [EmployeeProfileController::class, 'updatePassword']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////

});


///////////////////////////guest user/////////////////////////////////////////////////
Route::prefix('company')->middleware('throttle:20')->group(function () {
    Route::post('login', [EmployeeProfileController::class, 'login']);
    Route::post('forgot-password', [EmployeeProfileController::class, 'forgotPassword']);
    Route::post('reset-password', [EmployeeProfileController::class, 'resetPassword']);

});



Route::get('companies/{companyId}/available-products', [CompanyController::class, 'availableProducts']);


// pharmacy Order & Cart Routes
Route::get('/pharmacy/cart', [PharmacyOrderController::class, 'index']);
Route::post('/pharmacy/cart', [PharmacyOrderController::class, 'store']);
Route::delete('/pharmacy/cart', [PharmacyOrderController::class, 'destroy']);

// 🧾 pharmacy Order
Route::post('/pharmacy/orders', [PharmacyOrderController::class, 'storeOrder']);

Route::put('company/orders/{id}/status', [CompanyOrderController::class, 'updateStatus']);


Route::post('company/orders/{id}/assign', [CompanyOrderController::class, 'assignWarehouse']);

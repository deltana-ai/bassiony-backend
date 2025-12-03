<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\{ CompanyController, CompanyOfferController, CompanyOrderController, CompanyProductController, EmployeeProfileController, EmployeeRoleController, PharmacyOrderController, ResponseOfferController, WarehouseController, WarehouseProductController,WarehouseProductSearchController};
use App\Http\Controllers\Dashboard\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Dashboard\BrandController as AdminBrandController;
use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;
Route::middleware(['auth:employees'])->prefix('company/dashboard')->name('company.')->group(function () {



    /////////////////////////////////////// warehouses //////////////////////////////////////////////
    Route::post('warehouses/index', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('warehouses/restore', [WarehouseController::class, 'restore']);
    Route::delete('warehouses/delete', [WarehouseController::class, 'destroy']);
    Route::put('/warehouses/{id}/{column}', [WarehouseController::class, 'toggle']);
    Route::delete('warehouses/force-delete', [WarehouseController::class, 'forceDelete']);
    Route::apiResource('warehouses', WarehouseController::class)->except(['index','destroy']);
    ///////////////////////////////////////////////////////////////////////////////////////////////





    ///////////////////////////////////update company data/////////////////////////////////////////
    Route::patch('/our-company', [CompanyController::class, 'update']);
    Route::get('/our-company', [CompanyController::class, 'show']);
    // Route::apiResource('our-company', CompanyController::class)->only(['show','update']);
    //////////////////////////////////////////////////////////////////////////////////////////////





    ////////////////////////////////// roles //////////////////////////////////////////////////////
    Route::post('roles/index', [EmployeeRoleController::class, 'index'])->name('roles.index');
    Route::delete('roles/delete', [EmployeeRoleController::class, 'destroy']);
    Route::get('permissions', [EmployeeRoleController::class, 'getPermissions']);
    Route::apiResource('roles', EmployeeRoleController::class)->except(['index','destroy']);
    //////////////////////////////////////////////////////////////////////////////////////////////





    ///////////////////////////////////////////////////////////////////////////////////////
    Route::post('brands/index', [AdminBrandController::class, 'index']);
    Route::apiResource('brands', AdminBrandController::class)->only(['show']);
    //////////////////////////////////////////////////////////////////////////////////////





    /////////////////////////////////////////////////////////////////////////////////
    Route::post('categories/index', [AdminCategoryController::class, 'index']);
    Route::apiResource('categories', AdminCategoryController::class)->only(['show']);
    ////////////////////////////////////////////////////////////////////////////////////////





    ////////////////////////////////// employee crud //////////////////////////////////////////////////////
    Route::post('employees/index', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('employees/restore', [EmployeeController::class, 'restore']);
    Route::delete('employees/delete', [EmployeeController::class, 'destroy']);
    Route::put('/employees/{id}/{column}', [EmployeeController::class, 'toggle']);
    Route::delete('employees/force-delete', [EmployeeController::class, 'forceDelete']);
    Route::put('/employees/assign-warehouse', [EmployeeController::class, 'assignWarehouse']);
    Route::put('/employees/assign-role', [EmployeeController::class, 'assignRole']);
    Route::apiResource('employees', EmployeeController::class)->except(['index','destroy']);
    //////////////////////////////////////////////////////////////////////////////////////////////





    ////////////////////////////////// warehouse product crud //////////////////////////////////////////////////////
    Route::post('warehouses/{warehouse}/products/index', [WarehouseProductController::class, 'index'])->name('warehouse.products.index')->middleware('throttle:60,1');
   // Route::delete('warehouses/{warehouse}/products/delete', [WarehouseProductController::class, 'destroy']);
    Route::post('warehouses/{warehouse}/products/store/batch',[WarehouseProductController::class,"addBatch"]);
    Route::put('warehouses/{warehouse}/products/update/batch',[WarehouseProductController::class,"updateBatchStock"]);
    Route::post('warehouses/{warehouse}/products/store',[WarehouseProductController::class,"addReservedStock"]);
    Route::post('warehouses/{warehouse}/products/import',[WarehouseProductController::class,"import"]);
    Route::apiResource('warehouses/{warehouse}/products', WarehouseProductController::class)->only(['show']);
    //////////////////////////////////////////////////////////////////////////////////////////////






    /////////////////////////////////show product details//////////////////////////////////////////////////
    Route::post('master-products/index', [CompanyProductController::class, 'productsAll']);
    Route::post('products-prices/store', [CompanyProductController::class, 'storePrice']);
    Route::patch('products-prices/update/{product}', [CompanyProductController::class, 'updatePrice']);
    Route::get('products-prices/{product}', [CompanyProductController::class,"showProductPrice"]);

    Route::get('master-products/{product}', [ProductController::class,"show"]);

    /////////////////////////////////////////////////////////////////////////////////////////////////////






    /////////////////////////////////////////////////////////////////////////////////////////
    Route::post('company-products/index', [CompanyProductController::class,"index"]);
    ////////////////////////////////////////////////////////////////////////////////////////






    //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('offers/index', [CompanyOfferController::class, 'index']);
    Route::delete('offers/delete', [CompanyOfferController::class, 'destroy']);
    Route::put('/offers/{id}/{column}', [CompanyOfferController::class, 'toggle']);
    Route::post('offers/restore', [CompanyOfferController::class, 'restore']);
    Route::delete('offers/force-delete', [CompanyOfferController::class, 'forceDelete']);
    Route::apiResource('offers', CompanyOfferController::class)->except(['destroy','index']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////





    //////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('response-offers/index', [ResponseOfferController::class, 'index']);
    Route::delete('response-offers/delete', [ResponseOfferController::class, 'destroy']);
    Route::put('response-offers/update/{id}', [ResponseOfferController::class, 'updateStatus']);
    Route::get('response-offers/{id}', [ResponseOfferController::class, 'show']);
    Route::post('response-offers/restore', [ResponseOfferController::class, 'restore']);
    Route::delete('response-offers/force-delete', [ResponseOfferController::class, 'forceDelete']);
    /////////////////////////////////////////////////////////////////////////////////////////////////





    ////////////////////////profile employee///////////////////////////////////////////////////////////////
    Route::post('logout', [EmployeeProfileController::class, 'logout']);
    Route::get('show-profile', [EmployeeProfileController::class, 'show']);
    Route::put('/update-profile',[EmployeeProfileController::class,'updateProfile'])->name('profile.update');
    Route::put('update-password', [EmployeeProfileController::class, 'updatePassword']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////





    
    ///////////////////////////////////////////////////////////////////////////////////////////////////
});

///////////////////////////////////////////////////////////////////////////////////////////////////
Route::middleware(['auth:employees'])->prefix('company')->name('company.')->group(function () {

    Route::put('orders/{id}/status', [CompanyOrderController::class, 'updateStatus']);
    Route::post('orders/{id}/assign', [CompanyOrderController::class, 'assignWarehouse']);
    Route::get('all-pharmacy-orders', [CompanyOrderController::class, 'getAllPharmacyOrders']);
});


///////////////////////////guest user/////////////////////////////////////////////////
Route::prefix('company')->middleware('throttle:20')->group(function () {
    Route::post('login', [EmployeeProfileController::class, 'login']);
    Route::post('forgot-password', [EmployeeProfileController::class, 'forgotPassword']);
    Route::post('reset-password', [EmployeeProfileController::class, 'resetPassword']);

});

Route::get('/warehouse-products/search', [WarehouseProductSearchController::class, 'search']);
Route::get('companies/{companyId}/available-products', [CompanyController::class, 'availableProducts']);


// pharmacy Order & Cart Routes
Route::get('/pharmacy/cart', [PharmacyOrderController::class, 'index']);
Route::post('/pharmacy/cart', [PharmacyOrderController::class, 'store']);
Route::delete('/pharmacy/cart', [PharmacyOrderController::class, 'destroy']);

// 🧾 pharmacy Order
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pharmacy/orders', [PharmacyOrderController::class, 'storeOrder']);
});



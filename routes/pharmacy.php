<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Dashboard\{
    BranchController,
    BranchProductController,
    CompanyController,
    CompanyOfferController,
    PharmacyOrderController,
    PharmacyProductController,
    PharmacyRoleController,
    ResponseOfferController
};

use App\Http\Controllers\{
    OrderController,
    ProductController,
    PharmacistController
};
use App\Http\Controllers\Dashboard\BrandController as AdminBrandController;
use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;


Route::middleware(['auth:pharmacists'])->prefix('pharmacy/dashboard')->name('pharmacy.')->group(function () {




      ///////////////////////////////////////////////////////////////////////////////////////
    Route::post('brands/index', [AdminBrandController::class, 'index']);
    Route::apiResource('brands', AdminBrandController::class)->only(['show']);
    //////////////////////////////////////////////////////////////////////////////////////




    
    /////////////////////////////////////////////////////////////////////////////////
    Route::post('categories/index', [AdminCategoryController::class, 'index']);
    Route::apiResource('categories', AdminCategoryController::class)->only(['show']);
    ////////////////////////////////////////////////////////////////////////////////////



    

    /////////////////////////////////////////////////////////////////////////////////
    Route::post('/pharmacist/index', [PharmacistController::class, 'index']);
    Route::post('pharmacist/restore', [PharmacistController::class, 'restore']);
    Route::delete('pharmacist/delete', [PharmacistController::class, 'destroy']);
    Route::put('/pharmacist/{id}/{column}', [PharmacistController::class, 'toggle']);
    Route::delete('pharmacist/force-delete', [PharmacistController::class, 'forceDelete']);
    Route::apiResource('pharmacist', PharmacistController::class)->only(["store","show","update"]);
    ////////////////////////////////////////////////////////////////////////////////////////////////




    /////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('companies/index', [CompanyController::class, 'index']);
    Route::apiResource('companies', CompanyController::class)->only(['show']);
    /////////////////////////////////////////////////////////////////////////////////////////////////





     ////////////////////////////////// roles //////////////////////////////////////////////////////
    Route::post('roles/index', [PharmacyRoleController::class, 'index'])->name('roles.index');
    Route::delete('roles/delete', [PharmacyRoleController::class, 'destroy']);
    Route::get('permissions', [PharmacyRoleController::class, 'getPermissions']);
 
    Route::apiResource('roles', PharmacyRoleController::class)->except(['index','destroy']);
    //////////////////////////////////////////////////////////////////////////////////////////////



    ///////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('branches/index', [BranchController::class, 'index']);
    Route::post('branches/restore', [BranchController::class, 'restore']);
    Route::delete('branches/delete', [BranchController::class, 'destroy']);
    Route::put('/branches/{id}/{column}', [BranchController::class, 'toggle']);
    Route::delete('branches/force-delete', [BranchController::class, 'forceDelete']);
    Route::apiResource('branches', BranchController::class)->only(["store","show","update"]);
    ///////////////////////////////////////////////////////////////////////////////////////////////////






    ///////////////////////////////////////////////////////////////////////////////////////
    Route::post('branches/{branch}/products/index', [BranchProductController::class, 'index'])->name('warehouse.products.index');
    Route::delete('branches/{branch}/products/delete', [BranchProductController::class, 'destroy']);
    Route::post('branches/{branch}/products/store/batch',[BranchProductController::class,"addBatch"]);
    Route::put('branches/{branch}/products/update/batch',[BranchProductController::class,"updateBatchStock"]);
    Route::post('branches/{branch}/products/store',[BranchProductController::class,"addReservedStock"]);
    Route::post('branches/{branch}/products/import',[BranchProductController::class,"import"]);
    Route::apiResource('branches/{branch}/products', BranchProductController::class)->only(['show']);
    //////////////////////////////////////////////////////////////////////////////////////////////





     /////////////////////////////////show product details//////////////////////////////////////////////////
    Route::post('master-products/index', [ProductController::class, 'index']);
    Route::get('master-products/{product}', [ProductController::class,"show"]);
    /////////////////////////////////////////////////////////////////////////////////////////////////////




     /////////////////////////////////////////////////////////////////////////////////////////
    Route::post('pharmacy-products/index', [PharmacyProductController::class,"index"]);

    ////////////////////////////////////////////////////////////////////////////////////////





    //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('company-offers/index', [CompanyOfferController::class, 'index']);
    Route::apiResource('company-offers', CompanyOfferController::class)->only(['show']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////





    //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::put('/response-company-offers/cancel/{id}', [ResponseOfferController::class, 'cancel']);
    Route::post('/response-company-offers/index', [ResponseOfferController::class, 'index']);
    Route::apiResource('response-company-offers', ResponseOfferController::class)->only(['show','store','update']);

    ///////////////////////////////////////////////////////////////////////////////////////////////////




    //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('user-orders', [CompanyOfferController::class, 'index']);
    Route::apiResource('user-orders', CompanyOfferController::class)->only(['show',"update",'destroy']);

    //////////////////////////////////////////////////////////////////////////////////////////////////////






});
Route::get('pharmacies/{id}/orders', [OrderController::class, 'getPharmacyOrders']);
Route::get('company/{companyId}/orders', [OrderController::class, 'companyOrders']);
Route::get('warehouses/{warehouse}/orders', [OrderController::class, 'ordersByWarehouse']);
Route::get('/company-all-products', [PharmacistController::class, 'indexAllProductInCompany'])
    ->name('company.products');

Route::prefix('pharmacist')->group(function () {
    Route::post('logout', [PharmacistController::class, 'logout'])
        ->middleware('auth:pharmacists');
});

Route::prefix('pharmacist')->middleware('throttle:20')->group(function () {
    Route::post('register', [PharmacistController::class, 'register']);
    Route::post('login', [PharmacistController::class, 'login']);
    Route::post('change-password', [PharmacistController::class, 'changePassword']);
});

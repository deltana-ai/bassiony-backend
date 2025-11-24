<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Dashboard\{
    BranchProductController,
    CompanyController,
    CompanyOfferController,
    PharmacyOrderController,
    PharmacyProductController,
    ResponseOfferController
};

use App\Http\Controllers\{
    OrderController,
    ProductController,
    PharmacistController
};


Route::middleware(['auth:pharmacists'])->prefix('pharmacy/dashboard')->name('pharmacy.')->group(function () {


    /////////////////////////////////////////////////////////////////////////////////
    Route::post('/pharmacist/index', [PharmacistController::class, 'index']);
    Route::post('pharmacist/restore', [PharmacistController::class, 'restore']);
    Route::delete('pharmacist/delete', [PharmacistController::class, 'destroy']);
    Route::put('/pharmacist/{id}/{column}', [PharmacistController::class, 'toggle']);
    Route::delete('pharmacist/force-delete', [PharmacistController::class, 'forceDelete']);
    Route::apiResource('pharmacist', PharmacistController::class);
    ////////////////////////////////////////////////////////////////////////////////////////////////




    /////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('companies/index', [CompanyController::class, 'index']);
    Route::apiResource('companies', CompanyController::class)->only(['show']);
    /////////////////////////////////////////////////////////////////////////////////////////////////




    ///////////////////////////////////////////////////////////////////////////////////////
    Route::post('branches/{branch}/products/index', [BranchProductController::class, 'index'])->name('warehouse.products.index');
    Route::delete('branches/{branch}/products/delete', [BranchProductController::class, 'destroy']);
    Route::post('branches/{branch}/products/store/batch',[BranchProductController::class,"addBatch"]);
    Route::post('branches/{branch}/products/store',[BranchProductController::class,"addReservedStock"]);
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
    Route::apiResource('response-company-offers', ResponseOfferController::class)->only(['show','store']);

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

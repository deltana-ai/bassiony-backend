<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\{ CompanyController, CompanyOfferController, 
    PharmacyOrderController, ResponseOfferController };
use App\Http\Controllers\ProductController;

Route::middleware(['auth:pharmacists'])->prefix('pharmacy/dashboard')->name('pharmacy.')->group(function () {


    /////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('companies/index', [CompanyController::class, 'index']);
    Route::apiResource('companies', CompanyController::class)->only(['show']);
    /////////////////////////////////////////////////////////////////////////////////////////////////



 


     /////////////////////////////////show product details//////////////////////////////////////////////////
    Route::post('product/index', [ProductController::class, 'index']);
    Route::get('product/{product}', [ProductController::class,"show"]);
    /////////////////////////////////////////////////////////////////////////////////////////////////////



    //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('company-offers/index', [CompanyOfferController::class, 'index']);
    Route::put('/company-offers/cancel/{companyOffer}', [CompanyOfferController::class, 'cancel']);
    Route::apiResource('company-offers', CompanyOfferController::class)->only(['show']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////


 //////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('user-orders', [CompanyOfferController::class, 'index']);
    Route::apiResource('user-orders', CompanyOfferController::class)->only(['show',"update",'destroy']);
    //////////////////////////////////////////////////////////////////////////////////////////////////////






});
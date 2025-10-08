<?php  

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\{ CompanyController, EmployeeProfileController, EmployeeRoleController, LocationController, WarehouseController, WarehouseProductController};
use App\Http\Controllers\Dashboard\EmployeeController;

Route::middleware(['auth:employees','employee.role:manager'])->prefix('company/dashboard')->name('company.')->group(function () {



    /////////////////////////////////////// warehouses //////////////////////////////////////////////
    Route::post('warehouses/index', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('warehouses/restore', [WarehouseController::class, 'restore']);
    Route::delete('warehouses/delete', [WarehouseController::class, 'destroy']);
    Route::put('/warehouses/{id}/{column}', [WarehouseController::class, 'toggle']);
    Route::delete('warehouses/force-delete', [WarehouseController::class, 'forceDelete']);
    Route::apiResource('warehouses', WarehouseController::class); 
    ///////////////////////////////////////////////////////////////////////////////////////////////



    //////////////////////////////////////locations/////////////////////////////////////////////////
    Route::post('locations/index', [LocationController::class, 'index']);
    Route::delete('locations/delete', [LocationController::class, 'destroy']);
    Route::apiResource('locations', LocationController::class); 
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
    Route::apiResource('roles', EmployeeRoleController::class); 
    //////////////////////////////////////////////////////////////////////////////////////////////



    ////////////////////////////////// employee crud //////////////////////////////////////////////////////
    Route::post('employees/index', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('employees/restore', [EmployeeController::class, 'restore']);
    Route::delete('employees/delete', [EmployeeController::class, 'destroy']);
    Route::put('/employees/{id}/{column}', [EmployeeController::class, 'toggle']);
    Route::delete('employees/force-delete', [EmployeeController::class, 'forceDelete']);
    Route::apiResource('employees', EmployeeController::class); 
    //////////////////////////////////////////////////////////////////////////////////////////////



    ////////////////////////////////// warehouse product crud //////////////////////////////////////////////////////
    Route::post('warehouses/{warehouse}/products/index', [WarehouseProductController::class, 'index'])->name('warehouse.products.index');
    Route::delete('warehouses/{warehouse}/products/delete', [WarehouseProductController::class, 'destroy']);
    Route::apiResource('warehouses/{warehouse}/products', WarehouseProductController::class); 
    //////////////////////////////////////////////////////////////////////////////////////////////








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
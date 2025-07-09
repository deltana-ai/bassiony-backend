<?php

use Illuminate\Support\Facades\Route;
use Modules\ClientModule\Http\Controllers\ClientModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clientmodules', ClientModuleController::class)->names('clientmodule');
});

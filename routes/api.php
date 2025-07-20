<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Api\Auth\{ClientAuthController,PharmacistAuthController,DriverAuthController};
use App\Http\Controllers\Api\Profile\{ClientProfileController,PharmacistProfileController,DriverProfileController};
use App\Http\Controllers\Common\Point\UserPointController;
// use App\Http\Controllers\Common\DosageReminder\DosesController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});




//////////////////////////////////////// team ////////////////////////////////



//////////////////////////////////////// logo company ////////////////////////////////


///////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => []], static function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    Route::get('/get-unused-media', [MediaController::class, 'getUnUsedImages']);
    Route::delete('/delete-unused-media', [MediaController::class, 'deleteUnUsedImages']);
    Route::post('/media/delete', [MediaController::class, 'deleteImagesByIds']);
});
Route::get('/get-media/{media}', [MediaController::class, 'show']);
Route::post('/media-array', [MediaController::class, 'showMedia']);
Route::post('/media-upload-many', [MediaController::class, 'storeMany']);




// Route::middleware(['resolve.guard', 'ensure.guard:App\Models\User'])->group(function () {
//     Route::post('/medicines', [DosesController::class, 'addMedicine']);
//     Route::post('/medicines-with-schedule', [DosesController::class, 'addMedicineWithSchedule']);
//     Route::post('/medicines/{medicine}/schedules', [DosesController::class, 'addSchedule']);
//     Route::post('/intakes/{intake}/mark-taken', [DosesController::class, 'markAsTaken']); // لن يُستخدم حالياً
//     Route::get('/intakes/by-day', [DosesController::class, 'getDosesByDay']); // اليوم المختار
//     Route::get('/intakes', [DosesController::class, 'getAllDoses']); // كل الأسبوع
//     Route::post('/medicines/continue-week', [DosesController::class, 'continueNextWeek']);

// });

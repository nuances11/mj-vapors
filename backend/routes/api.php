<?php

use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SettingCompanyController;
use App\Http\Controllers\API\SettingUserController;
use App\Http\Controllers\API\SkuController;
use App\Http\Controllers\API\TimeTrackingController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('users/report/{id}', UserReportController::class);


Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('users/{id}/check-password', [UserController::class, 'checkPassword']);
    Route::delete('attributes/option/{id}', [AttributeController::class, 'deleteOption']);
    Route::get('attributes/get-options', [AttributeController::class, 'getOptionSelection']);
    Route::post('transactions/reports/export-to-csv', [TransactionController::class, 'exportToCsv']);
    Route::get('transactions/{id}/items', [TransactionController::class, 'getTransactionItems']);

    Route::patch('transactions/{id}/update-status', [TransactionController::class, 'updateTransactionStatus']);

    Route::post('time-trackings/log-time', [TimeTrackingController::class, 'logTime']);
    Route::get('time-trackings/check-log-data', [TimeTrackingController::class, 'checkLogData']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('attributes', AttributeController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('listings', SkuController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('time-trackings', TimeTrackingController::class);



    Route::prefix('/setting')->group(function () {
        Route::put('company/upload-logo/{id}', [SettingCompanyController::class, 'uploadLogo']);
        Route::apiResource('company', SettingCompanyController::class);
        Route::apiResource('user', SettingUserController::class);
    });
//    Route::apiResource('skus', SkuController::class);
});

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

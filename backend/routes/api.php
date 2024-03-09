<?php

use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SkuController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
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


Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('users/{id}/check-password', [UserController::class, 'checkPassword']);
    Route::delete('attributes/option/{id}', [AttributeController::class, 'deleteOption']);
    Route::get('attributes/get-options', [AttributeController::class, 'getOptionSelection']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('attributes', AttributeController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('listings', SkuController::class);
});

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

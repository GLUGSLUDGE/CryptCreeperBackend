<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlayController;
use App\Http\Middleware\ValidateToken;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/user')->group(function() {
    Route::put('/create',[UserController::class, 'create']);
    Route::post('/login',[UserController::class, 'login']);
    Route::get('/getUserData',[UserController::class,'getUserData'])->middleware('auth:sanctum');
    Route::get('/getTop8',[UserController::class,'getTop8'])->middleware('auth:sanctum');
    Route::post('/change-name',[UserController::class,'changeName'])->middleware('auth:sanctum');
    Route::post('/change-password',[UserController::class,'changePassword'])->middleware('auth:sanctum');
    Route::post('/change-photo', [UserController::class,'changePhoto'])->middleware('auth:sanctum');
    Route::post('/logout', [UserController::class,'logout'])->middleware('auth:sanctum');
    Route::delete('/delete-user', [UserController::class,'deleteUser'])->middleware('auth:sanctum');
});

Route::prefix('/play')->group(function() {
    Route::post('/save_points',[PlayController::class, 'save_points'])->middleware('auth:sanctum');
    Route::get('/get_higher_points',[PlayController::class, 'get_higher_points'])->middleware('auth:sanctum');
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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
    Route::post('/change-name',[UserController::class,'changeName'])->middleware('auth:sanctum');
    Route::post('/change-password',[UserController::class,'changePassword'])->middleware('auth:sanctum');
    Route::post('/change-photo', [UserController::class,'changePhoto'])->middleware('auth:sanctum');
    Route::post('/logout', [UserController::class,'logout'])->middleware('auth:sanctum');
    Route::delete('/delate-user', [UserController::class,'daleteUser'])->middleware('auth:sanctum');
  
    
    
});



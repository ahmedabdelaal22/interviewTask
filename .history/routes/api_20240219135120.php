<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsersController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api','cors']], function() {
    Route::post('login', [AuthController::class, 'authenticate']);
    Route::post('register', [AuthController::class, 'register']);
    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('get_user', [AuthController::class, 'get_user']);
        Route::get('products', [UsersController::class, 'index']);
        Route::get('products/{id}', [UsersController::class, 'show']);
        Route::post('create', [UsersController::class, 'store']);
        Route::put('update/{product}',  [UsersController::class, 'update']);
        Route::delete('delete/{product}',  [UsersController::class, 'destroy']);
    });     
});



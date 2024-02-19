<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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
        // Route::get('products', [ProductController::class, 'index']);
        // Route::get('products/{id}', [ProductController::class, 'show']);
        // Route::post('create', [ProductController::class, 'store']);
        // Route::put('update/{product}',  [ProductController::class, 'update']);
        // Route::delete('delete/{product}',  [ProductController::class, 'destroy']);
    });     
});



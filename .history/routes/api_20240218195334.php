<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api','cors']], function() {
    Route::post('login', [ApiController::class, 'authenticate']);
    Route::post('register', [ApiController::class, 'register']);
    Route::group(['middleware' => ['jwt.verify']], function() {
        
    });     
});



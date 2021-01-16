<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

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

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [Api\AuthController::class,'login']);
    Route::post('register', [Api\AuthController::class,'register']);

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', [Api\AuthController::class,'logout']);
        Route::get('user', [Api\AuthController::class,'user']);
    });
});



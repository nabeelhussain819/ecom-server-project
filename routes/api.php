<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

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
//    Route::post('login', [Api\AuthController::class,'login']);
//    Route::post('register', [Api\AuthController::class,'register']);

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', [Api\AuthController::class,'logout']);
        Route::get('user', [Api\AuthController::class,'user']);
    });
});

Route::get('categories',[Api\CategoryController::class,'index']);
Route::post('categories',[Api\CategoryController::class,'store']);
Route::get('products',[Api\ProductController::class,'index']);
Route::post('products',[Api\ProductController::class,'store']);
Route::patch('products/{id}',[Api\ProductController::class,'update']);
Route::delete('products/{id}',[Api\ProductController::class,'destroy']);


//
Route::group(['prefix'=>'/auth',['middleware'=>'throttle:20,5']],function (){
    Route::post('/register',[Api\Auth\RegisterController::class,'register']);
    Route::post('/login', [Api\Auth\LoginController::class, 'login']);
});

//Secure routes
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('categories-secure', [Api\CategoryController::class, 'index']);
});
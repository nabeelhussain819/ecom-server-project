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
Route::get('categories/{category}',[Api\CategoryController::class,'show']);
Route::post('categories',[Api\CategoryController::class,'store']);
Route::get('products',[Api\ProductController::class,'index']);
Route::post('products',[Api\ProductController::class,'store']);
//Route::patch('products/{id}',[Api\ProductController::class,'update']);
Route::delete('products/{id}',[Api\ProductController::class,'destroy']);


//
Route::group(['prefix'=>'/auth',['middleware'=>'throttle:20,5']],function (){
    Route::post('/register',[Api\Auth\RegisterController::class,'register']);
    Route::post('/login', [Api\Auth\LoginController::class, 'login']);
    Route::post('/facebook-login', [Api\Auth\LoginController::class, 'facebookLogin']);
    Route::post('/google-login', [Api\Auth\LoginController::class, 'googleLogin']);
});

//all the below route should be in Secure routes
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('categories-secure', [Api\CategoryController::class, 'index']);
});


Route::group(['prefix' => '/categories', ['middleware' => 'throttle:20,5']], function () {
    Route::get('/product-attributes/{category}', [Api\CategoryController::class, 'productAttributes']);
});
Route::group(['prefix' => '/products', ['middleware' => 'throttle:20,5']], function () {
    Route::get('/show/{product:guid}', [Api\ProductController::class, 'show']);
    Route::patch('/{product:guid}', [Api\ProductController::class, 'update']);
    Route::post('upload/{product:guid}', [Api\ProductController::class, 'upload']);
    Route::get('media/{product:guid}', [Api\ProductController::class, 'media']);
});

Route::get('products',[Api\ProductController::class,'index']);
Route::post('forgot-password', [Api\Auth\ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [Api\Auth\ResetPasswordController::class, 'reset']);

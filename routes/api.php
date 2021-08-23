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

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', [Api\AuthController::class, 'logout']);
        Route::get('user', [Api\AuthController::class, 'user']);
    });
});


Route::get('products', [Api\ProductController::class, 'index']);
//Route::patch('products/{id}',[Api\ProductController::class,'update']);
Route::delete('products/{id}', [Api\ProductController::class, 'destroy']);


//
Route::group(['prefix' => '/auth', ['middleware' => 'throttle:20,5']], function () {
    Route::post('/register', [Api\Auth\RegisterController::class, 'register']);
    Route::post('/login', [Api\Auth\LoginController::class, 'login']);
    Route::post('/facebook-login', [Api\Auth\LoginController::class, 'facebookLogin']);
    Route::post('/google-login', [Api\Auth\LoginController::class, 'googleLogin']);
});

//===============================All the below route should be in Secure routes==============================
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('categories-secure', [Api\CategoryController::class, 'index']);

    Route::group(['prefix' => '/categories'], function () {
        Route::get('/tabs', [Api\CategoryController::class, 'tabs']);
        Route::get('/', [Api\CategoryController::class, 'index']);
//        Route::get('categories', [Api\CategoryController::class, 'index']);
        Route::get('/{category}', [Api\CategoryController::class, 'show']);
        Route::post('/', [Api\CategoryController::class, 'store']);
    });

    Route::group(['prefix' => '/user'], function () {
        Route::get('detail/', [Api\UserController::class, 'detail']);
        Route::post('upload', [Api\UserController::class, 'upload']);
        Route::get('conversations', [Api\UserController::class, 'conversations']);
        Route::get('{user}/messages', [Api\UserController::class, 'messages']);
        Route::post('{user}/send-message', [Api\UserController::class, 'sendMessage']);
    });

    Route::group(['prefix' => '/products'], function () {
        Route::post('/', [Api\ProductController::class, 'store']);
        Route::patch('/{product:guid}', [Api\ProductController::class, 'update']);
        Route::get('/self/', [Api\ProductController::class, 'self']);
        Route::post('upload/{product:guid}', [Api\ProductController::class, 'upload']);
        Route::post('saved-users/{product:guid}', [Api\ProductController::class, 'saved']);
        Route::post('/{product:guid}/offer', [Api\ProductController::class, 'offer']);
    });

    Route::group(['prefix' => '/services'], function () {
        Route::get('/', [Api\ServiceController::class, 'index']);
        Route::get('/show/{service:guid}', [Api\ServiceController::class, 'show']);
        Route::post('/', [Api\ServiceController::class, 'store']);
        Route::patch('/{service:guid}', [Api\ServiceController::class, 'update']);
        Route::get('media/{service:guid}', [Api\ServiceController::class, 'media']);
    });

    Route::get('/message/conversations', [Api\MessageController::class, 'conversations']);
    Route::Resources([
        'message' => \Api\MessageController::class
    ]);

});
//===============================All the below route should be in Secure routes==============================

//====================================== PUBLIC ROUTES =========================================

Route::group(['prefix' => '/categories', ['middleware' => 'throttle:20,5']], function () {
    Route::get('/product-attributes/{category}', [Api\CategoryController::class, 'productAttributes']);
});

Route::group(['prefix' => '/products'], function () {
    Route::get('/show/{product:guid}', [Api\ProductController::class, 'show']);
    Route::get('media/{product:guid}', [Api\ProductController::class, 'media']);
    Route::get('/search', [Api\ProductController::class, 'search']);
});

Route::group(['prefix' => '/services'], function () {
    Route::get('/search', [Api\ServiceController::class, 'search']);
});

Route::get('products', [Api\ProductController::class, 'index']);
Route::post('forgot-password', [Api\Auth\ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [Api\Auth\ResetPasswordController::class, 'reset']);


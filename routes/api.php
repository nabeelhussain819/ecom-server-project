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

Route::post('auth/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verifyRegisterUser']);
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
        Route::patch('/', [Api\UserController::class, 'update']);
    });

    Route::group(['prefix' => '/products'], function () {
        Route::post('/', [Api\ProductController::class, 'store']);
        Route::patch('/{product:guid}', [Api\ProductController::class, 'update']);
        Route::get('/self/', [Api\ProductController::class, 'self']);
        Route::post('upload/{product:guid}', [Api\ProductController::class, 'upload']);
        Route::post('saved-users/{product:guid}', [Api\ProductController::class, 'saved']);
        Route::get('saved', [Api\ProductController::class, 'getSaved']);
        Route::post('/{product:guid}/offer', [Api\ProductController::class, 'offer']);
        Route::delete('media/{media:guid}', [Api\ProductController::class, 'deleteMedia']);;
    });

    Route::group(['prefix' => '/services'], function () {
        Route::get('/', [Api\ServiceController::class, 'index']);
        Route::get('/self/', [Api\ServiceController::class, 'self']);
        Route::post('/', [Api\ServiceController::class, 'store']);
        Route::patch('/{service:guid}', [Api\ServiceController::class, 'update']);
        Route::get('media/{service:guid}', [Api\ServiceController::class, 'media']);
        Route::post('upload/{service:guid}', [Api\ServiceController::class, 'upload']);
    });


    Route::get('/message/conversations', [Api\MessageController::class, 'conversations']);
    Route::post('/message/saveAssociated/{message}', [Api\MessageController::class, 'saveAssociated']);
    Route::Resources([
        'message' => \Api\MessageController::class
    ]);

});
//===============================All the below route should be in Secure routes==============================

//====================================== PUBLIC ROUTES =========================================

Route::group(['prefix' => '/categories', ['middleware' => 'throttle:20,5']], function () {
    Route::get('/tabs', [Api\CategoryController::class, 'tabs']);
    Route::get('tabs/list', [Api\CategoryController::class, 'tabs']);
    Route::get('/product-attributes/{category}', [Api\CategoryController::class, 'productAttributes']);

});

Route::group(['prefix' => '/products'], function () {
    Route::get('/show/{product:guid}', [Api\ProductController::class, 'show']);
    Route::get('media/{product:guid}', [Api\ProductController::class, 'media']);
    Route::get('/search', [Api\ProductController::class, 'search']);
});

Route::group(['prefix' => '/services'], function () {
    Route::get('/search', [Api\ServiceController::class, 'search']);
    Route::get('/show/{service:guid}', [Api\ServiceController::class, 'show']);
});

Route::get('products', [Api\ProductController::class, 'index']);
Route::post('forgot-password', [Api\Auth\ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [Api\Auth\ResetPasswordController::class, 'reset']);



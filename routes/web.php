<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::Resources([
        'category' => CategoryController::class,
        'products' => ProductController::class,
        'services' => ServiceController::class,
        'attribute' => AttributeController::class,
        'unit-type' => UnitTypeController::class,
        'media' => MediaController::class
    ]);


    Route::get('category', 'CategoryController@search')->name('category.search');

    //let me know when  we move that in1 view and 1 route
    // @todo add the some comment's on the route why you need it
    Route::get('in-active-category', 'CategoryController@inActive')->name('category.in-active'); //@todo move that route in category prefix and other as well
    Route::get('in-active-category', 'CategoryController@searchInActive')->name('category.inactive.search');
    Route::post('in-activate-category/all','CategoryController@activateAll')->name('categories.active-all');
    Route::get('products', 'ProductController@search')->name('products.search');
    Route::get('in-active-products', 'ProductController@inActive')->name('products.in-active');
    Route::get('in-active-products', 'ProductController@searchInActive')->name('products.inactive.search');
    Route::post('in-activate-products/all','ProductController@activateAll')->name('products.active-all');
    Route::get('services', 'ServiceController@search')->name('services.search');
    Route::get('in-active-services', 'ServiceController@inActive')->name('services.in-active');
    Route::get('in-active-services', 'ServiceController@searchInActive')->name('services.in-active.search');
    Route::post('in-activate-services/all','ServiceController@activateAll')->name('services.active-all');
    Route::get('products/customer/{user}','UserController@showUserProducts')->name('customer.products');
    Route::get('services/customer/{user}','UserController@showUserServices')->name('customer.services');
    Route::post('in-activate-products/customer/{user}','UserController@activateAllProducts')->name('customer.products.active-all');
    Route::post('in-activate-services/customer/{user}','UserController@activateAllServices')->name('customer.services.active-all');

    Route::group(['prefix' => 'category/properties'], function () {
        Route::get('show-list/{category:guid}', 'CategoryController@showAttributesList')->name('category.show-list');
        Route::get('show/{category:guid}', 'CategoryController@showAttributes')->name('category.show-attributes');
        Route::post('add/{category:guid}', 'CategoryController@addAttributes')->name('category.add-attributes');
    });
});

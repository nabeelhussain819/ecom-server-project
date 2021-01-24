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
Route::group(['prefix'=>'admin','middleware' => 'auth'], function(){
    Route::Resources([
       'category' => CategoryController::class,
       'products' => ProductController::class,
        'services' => ServiceController::class,
        'attribute' => AttributeController::class
    ]);
    Route::get('category','CategoryController@search')->name('category.search');
    Route::get('in-active-category','CategoryController@inActive')->name('category.in-active');
    Route::get('in-active-category','CategoryController@searchInActive')->name('category.inactive.search');
    Route::get('products','ProductController@search')->name('products.search');
    Route::get('in-active-products','ProductController@inActive')->name('products.in-active');
    Route::get('in-active-products','ProductController@searchInActive')->name('products.inactive.search');
    Route::get('services','ServiceController@search')->name('services.search');
    Route::get('in-active-services','ServiceController@inActive')->name('services.in-active');
    Route::get('in-active-services','ServiceController@searchInActive')->name('services.in-active.search');

});

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

Route::prefix('users')->group(function () {
    Route::get('/', 'UserController@index');
    Route::post('/', 'UserController@store');
    Route::get('/create', 'UserController@create');
    Route::get('/{id}', 'UserController@detail');
    Route::get('/{id}/edit', 'UserController@edit');
    Route::post('/{id}/edit', 'UserController@update');
    Route::delete('/{id}/delete', 'UserController@delete');
    Route::post('/delete-selected', 'UserController@deleteSelected');

});
Route::prefix('categories')->group(function () {
    Route::get('/', 'CategoryController@index');
    Route::post('/', 'CategoryController@store');
    Route::get('/create', 'CategoryController@create');
    Route::get('/{id}', 'CategoryController@detail');
    Route::get('/{id}/edit', 'CategoryController@edit');
    Route::post('/{id}/edit', 'CategoryController@update');
    Route::post('/{id}/updatePosition', 'CategoryController@updatePosition');
    Route::delete('/{id}/delete', 'CategoryController@delete');
});

Route::prefix('pics')->group(function () {
    Route::get('/', 'PicController@index');
    Route::post('/', 'PicController@store');
    Route::get('/create', 'PicController@create');
    Route::get('/{id}', 'PicController@detail');
    Route::get('/{id}/edit', 'PicController@edit');
    Route::post('/{id}/edit', 'PicController@update');
    Route::post('/{id}/updatePosition', 'PicController@updatePosition');
    Route::delete('/{id}/delete', 'PicController@delete');
    Route::post('/delete-selected', 'PicController@deleteSelected');
});
Route::get('/login', 'AuthController@login');
Route::post('/login', 'AuthController@loginProcess');
Route::get('/logout', 'AuthController@logout');
Route::get('/index', function(){
    return redirect('/cms/users');
});

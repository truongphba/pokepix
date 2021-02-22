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
});
Route::prefix('categories')->group(function () {
    Route::get('/{name}/list', 'CategoryController@index');
    Route::post('/', 'CategoryController@store');
    Route::get('/create', 'CategoryController@create');
    Route::get('/{name}/{id}', 'CategoryController@detail');
    Route::get('/{name}/{id}/edit', 'CategoryController@edit');
    Route::post('/{name}/{id}/edit', 'CategoryController@update');
    Route::post('/{name}/{id}/updatePosition', 'CategoryController@updatePosition');
    Route::delete('/{name}/{id}/delete', 'CategoryController@delete');
});
Route::get('/login', 'AuthController@login');
Route::post('/login', 'AuthController@loginProcess');
Route::get('/logout', 'AuthController@logout');

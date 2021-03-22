<?php

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

use Illuminate\Support\Facades\Route;

Route::prefix('/')->middleware('device', 'throttle:150,1')->group(function() {

    Route::group(['prefix' => '/user'], function () {
    	Route::get('/', 'V1\UserController@info')->middleware('user');
        Route::get('/find/{id}', 'V1\UserController@find');
    	Route::post('/', 'V1\UserController@create');
    	Route::put('/','V1\UserController@update')->middleware('user');
    	Route::post('/avatar', 'V1\UserController@uploadAvatar')->middleware('user');

        Route::post('/follow', 'V1\UserController@follow')->middleware('user');
        Route::get('/images-liked/{id?}', 'V1\UserController@imagesLiked')->middleware('user');
        Route::get('/followers', 'V1\UserController@followers')->middleware('user');
        Route::get('/followings', 'V1\UserController@followings')->middleware('user');
        Route::get('/{id}/images', 'V1\UserController@images')->middleware('user');
        Route::get('/top', 'V1\UserController@topUser')->middleware('user');

	});

    Route::group(['prefix' => '/app'], function () {
        Route::post('/version', 'V1\UserController@topUser');
        Route::post('/version', 'V1\UserController@topUser')->middleware('user');
    });

    Route::group(['prefix' => '/image'], function () {
        Route::get('/update', 'V1\ImageController@update')->middleware('user');

        Route::get('/popular', 'V1\ImageController@popular')->middleware('user');
        Route::get('/news', 'V1\ImageController@news')->middleware('user');

    	Route::get('/{id}', 'V1\ImageController@info')->middleware('user');
    	Route::get('/{id}/user-like', 'V1\ImageController@listUsersLiked')->middleware('user');
    	Route::post('/', 'V1\ImageController@create')->middleware('user');
    	Route::post('like/{id}','V1\ImageController@likePost')->middleware('user');
    	Route::get('comment/{id}','V1\ImageController@listComment')->middleware('user');
    	Route::post('comment/{id}','V1\ImageController@comment')->middleware('user');
    	Route::post('/upload', 'V1\ImageController@upload')->middleware('user');

        Route::get('/category/{id}', 'V1\ImageController@listByCategory')->middleware('user');
        Route::get('/theme/{id}', 'V1\ImageController@listByTheme')->middleware('user');
    });

    Route::get('/category','V1\CategoryController@list')->middleware('user');
});


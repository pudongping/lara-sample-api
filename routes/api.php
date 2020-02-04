<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('Auth')
    ->group(function () {

    });

// 用户注册
Route::post('register', 'Auth\UsersController@register')->name('users.register');

Route::namespace('Api')
    ->group(function () {


        Route::get('tests', 'ApiTestsController@index')->name('tests.index');

    });

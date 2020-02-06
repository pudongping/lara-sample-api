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

Route::middleware('throttle:' . config('api.rate_limits.sign'))
    ->group(function () {

        // 图片验证码
        Route::post('captchas', 'Auth\CaptchasController@store')->name('captchas.store');
        // 用户注册
        Route::post('register', 'Auth\UsersController@register')->name('users.register');
        // 用户名/邮箱/手机号/登录
        Route::post('authorizations', 'Auth\UsersController@login')->name('api.authorizations.login');
        // 第三方登录
        Route::post('socials/{social_type}/authorizations', 'Auth\UsersController@socialStore')->name('socials.authorizations.store');

        // 刷新token
        Route::put('authorizations/current', 'Auth\UsersController@refreshToken')->name('authorizations.refreshToken');
        // 删除token
        Route::delete('authorizations/current', 'Auth\UsersController@logout')->name('authorizations.logout');

    });

Route::namespace('Api')
    ->group(function () {

        Route::middleware('throttle:' . config('api.rate_limits.access'))
            ->group(function () {

                Route::get('tests', 'ApiTestsController@index')->name('tests.index');
                Route::get('tt', 'ApiTestsController@tt')->name('tests.tt');

            });
    });

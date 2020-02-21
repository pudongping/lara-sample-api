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


/*
|--------------------------------------------------------------------------
| 门户相关
|--------------------------------------------------------------------------
|
*/
Route::group([
    'middleware' => ['throttle:' . config('api.rate_limits.sign')],  // 1分钟/10次
    'as' => 'api.'
], function () {
    // 图片验证码
    Route::post('captchas', 'Auth\CaptchasController@store')->name('captchas.store');
    // 短信验证码
    Route::post('verificationCodes', 'Auth\VerificationCodesController@store')->name('verificationCodes.store');
    // 用户注册
    Route::post('register', 'Auth\UsersController@register')->name('users.register');
    // 用户名/邮箱/手机号/登录
    Route::post('authorizations', 'Auth\UsersController@login')->name('authorizations.login');
    // 第三方登录
    Route::post('socials/{social_type}/authorizations', 'Auth\UsersController@socialStore')->name('socials.authorizations.store');
});

Route::group([
    'middleware' => ['throttle:' . config('api.rate_limits.access')],  // 1分钟/60次
    'as' => 'api.'
], function () {

    // 不需要登录就可以访问的
    // 某个用户的详情
    Route::get('users/{user}', 'Auth\UsersController@show')->name('users.show');

    // 登录后可以访问的接口
    Route::middleware(['auth:api'])->group(function () {
        // 刷新token
        Route::put('authorizations/current', 'Auth\UsersController@refreshToken')->name('authorizations.refreshToken');
        // 删除token
        Route::delete('authorizations/current', 'Auth\UsersController@logout')->name('authorizations.logout');
        // 当前登录用户信息
        Route::get('user', 'Auth\UsersController@me')->name('user.show');
        // 编辑登录用户信息
        Route::patch('user', 'Auth\UsersController@update')->name('user.update');

        // 上传图片
        Route::post('images', 'Common\ImagesController@store')->name('images.store');
        // 抽奖
        Route::get('prizes', 'Common\PrizesController@lottery')->name('prizes.lottery');


        Route::get('tests', 'Api\ApiTestsController@index')->name('tests.index');


    });


});


/*
|--------------------------------------------------------------------------
| 后台管理相关
|--------------------------------------------------------------------------
|
*/
Route::group([
    'middleware' => ['throttle:' . config('api.rate_limits.sign')],  // 1分钟/10次
    'prefix' => 'admin',
    'as' => 'admin.'
], function () {
    // 用户名/邮箱/手机号/登录
    Route::post('authorizations', 'Auth\AdminsController@login')->name('authorizations.login');
});

Route::group([
    'middleware' => ['throttle:' . config('api.rate_limits.access')],  // 1分钟/60次
    'prefix' => 'admin',
    'as' => 'admin.'
], function () {
    // 登录之后才允许访问
    Route::group(['middleware' => ['auth:admin', 'check_admin_menus']], function () {

        // 只有超级管理员才允许访问
        Route::group(['middleware' => ['role:Administrator']], function () {
            // 角色
            Route::resource('roles', 'Auth\RolesController')->except('show');
            Route::delete('roles_mass_destroy', 'Auth\RolesController@massDestroy')->name('roles.mass_destroy');
            // 权限
            Route::resource('permissions', 'Auth\PermissionsController')->except('show');
            Route::delete('permissions_mass_destroy', 'Auth\PermissionsController@massDestroy')->name('permissions.mass_destroy');
            // 管理员列表
            Route::get('users', 'Auth\AdminsController@index')->name('users.index');
            // 创建新管理员-数据处理
            Route::post('users', 'Auth\AdminsController@store')->name('users.store');
            // 某个用户的详情
            Route::get('users/{user}', 'Auth\AdminsController@show')->name('users.show');
            // 删除用户
            Route::delete('/users/{user}', 'Auth\AdminsController@destroy')->name('users.destroy');
            // 操作日志列表
            Route::get('logs', 'Admin\Setting\LogsController@index')->name('logs.index');
            // 站点设置
            Route::get('settings', 'Admin\Setting\SettingsController@index')->name('settings.index');
            // 更新站点设置
            Route::put('settings/update', 'Admin\Setting\SettingsController@update')->name('settings.update');
        });

        // 当前登录用户信息
        Route::get('user', 'Auth\AdminsController@me')->name('user.show');
        // 刷新token
        Route::put('authorizations/current', 'Auth\AdminsController@refreshToken')->name('authorizations.refreshToken');
        // 删除token
        Route::delete('authorizations/current', 'Auth\AdminsController@logout')->name('authorizations.logout');
        // 上传图片
        Route::post('images', 'Common\ImagesController@store')->name('images.store');
        // 编辑登录用户信息-数据处理
        Route::patch('users/{user}', 'Auth\AdminsController@update')->name('users.update');
        // 菜单
        Route::resource('menus', 'Admin\Setting\MenusController')->except(['create', 'show']);
        // 清空所有缓存
        Route::get('clear_cache', 'Admin\Setting\SettingsController@clearCache')->name('settings.clear_cache');
        // 抽奖概率测试
        Route::get('prizes/probably', 'Common\PrizesController@probably')->name('prizes.probably');

    });
});

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
    // 普通注册方式
    // 图片验证码
    Route::post('captchas', 'Auth\CaptchasController@store')->name('captchas.store');
    // 验证注册字段合法性
    Route::post('checkRegister', 'Auth\UsersController@checkRegister')->name('users.checkRegister');
    // 用户注册
    Route::post('register', 'Auth\UsersController@register')->name('users.register');

    // 第三方登录注册方式
    // 检验第三方登录是否已经绑定了手机号
    Route::post('checkBoundPhone/{social_type}', 'Auth\UsersController@checkBoundPhone')->name('users.checkBoundPhone');
    // 第三方登录（直接采用 openid 登录，前端已授权）
    Route::post('socials/{social_type}/login', 'Auth\UsersController@socialLogin')->name('socials.socialLogin');
    // 第三方登录（后端授权）
    Route::post('socials/{social_type}/authorizations', 'Auth\UsersController@socialStore')->name('socials.authorizations.store');
    // 用户名/邮箱/手机号/登录
    Route::post('authorizations', 'Auth\UsersController@login')->name('authorizations.login');
    // 找回密码
    Route::patch('resetPassword', 'Auth\UsersController@resetPassword')->name('users.resetPassword');
    // 短信验证码
    Route::post('verificationCodes', 'Auth\VerificationCodesController@store')->name('verificationCodes.store');
});

Route::group([
    'middleware' => ['throttle:' . config('api.rate_limits.access')],  // 1分钟/60次
    'as' => 'api.'
], function () {

    // 不需要登录就可以访问的
    Route::get('tests', 'Api\ApiTestsController@index')->name('tests.index');
    // 某个用户的详情
    Route::get('users/{user}', 'Auth\UsersController@show')->name('users.show');

    // =======================商品相关=========================
    Route::group(['prefix' => 'product'], function () {
        // 商品类目树型结构
        Route::get('allCateTree', 'Api\Product\ProductSpuController@allCateTree')->name('product.spus.allCateTree');
        // 所有的商品品牌
        Route::get('allBrands', 'Api\Product\ProductSpuController@allBrands')->name('product.spus.allBrands');
        // 商品列表
        Route::get('spus', 'Api\Product\ProductSpuController@index')->name('product.spus.index');
        // 商品详情
        Route::get('detail', 'Api\Product\ProductSpuController@detail')->name('product.spus.detail');
    });


    // 登录后可以访问的接口
    Route::middleware(['auth:api'], 'api_refresh_token')->group(function () {
        // 刷新token
        Route::put('authorizations/refreshToken', 'Auth\UsersController@refreshToken')->name('authorizations.refreshToken');
        // 删除token
        Route::delete('authorizations/logout', 'Auth\UsersController@logout')->name('authorizations.logout');
        // 当前登录用户信息
        Route::get('user', 'Auth\UsersController@me')->name('user.show');
        // 编辑登录用户信息
        Route::patch('user', 'Auth\UsersController@update')->name('user.update');
        // 绑定第三方授权账号（客户端直接提供 openid）
        Route::patch('user/{social_type}/bound', 'Auth\UsersController@boundSocial')->name('user.boundSocial');

        // 收获地址
        Route::get('userAddresses', 'Auth\UserAddressesController@index')->name('userAddresses.index');
        // 创建收获地址
        Route::post('userAddresses', 'Auth\UserAddressesController@store')->name('userAddresses.store');
        // 编辑收货地址-显示数据
        Route::get('userAddresses/{user_address}/edit', 'Auth\UserAddressesController@edit')->name('userAddresses.edit');
        // 编辑收获地址-数据提交
        Route::put('userAddresses/{user_address}', 'Auth\UserAddressesController@update')->name('userAddresses.update');
        // 删除收货地址
        Route::delete('userAddresses/{user_address}', 'Auth\UserAddressesController@destroy')->name('userAddresses.destroy');

        // 上传图片
        Route::post('images', 'Common\ImagesController@store')->name('images.store');
        // 抽奖
        Route::get('prizes', 'Common\PrizesController@lottery')->name('prizes.lottery');



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
            Route::delete('rolesMassDestroy', 'Auth\RolesController@massDestroy')->name('roles.massDestroy');
            // 权限
            Route::resource('permissions', 'Auth\PermissionsController')->except('show');
            Route::delete('permissionsMassDestroy', 'Auth\PermissionsController@massDestroy')->name('permissions.massDestroy');
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
        Route::get('clearCache', 'Admin\Setting\SettingsController@clearCache')->name('settings.clearCache');
        // 抽奖概率测试
        Route::get('prizes/probably', 'Common\PrizesController@probably')->name('prizes.probably');

        // =======================商品相关=========================
        Route::group(['prefix' => 'product'], function () {
            // 商品类目列表
            Route::get('categories', 'Admin\Product\ProductCategoryController@index')->name('product.categories.index');
            // 商品类目树型结构
            Route::get('categoryTree', 'Admin\Product\ProductCategoryController@categoryTree')->name('product.categories.categoryTree');
            // 新建类目
            Route::post('categories', 'Admin\Product\ProductCategoryController@store')->name('product.categories.store');
            // 编辑显示类目
            Route::get('categories/{category}/edit', 'Admin\Product\ProductCategoryController@edit')->name('product.categories.edit');
            // 编辑类目-数据提交
            Route::patch('categories/{category}', 'Admin\Product\ProductCategoryController@update')->name('product.categories.update');
            // 删除类目
            Route::delete('categories/{category}', 'Admin\Product\ProductCategoryController@destroy')->name('product.categories.destroy');

            // 商品品牌列表
            Route::get('brands', 'Admin\Product\ProductBrandController@index')->name('product.brands.index');
            // 新建品牌
            Route::post('brands', 'Admin\Product\ProductBrandController@store')->name('product.brands.store');
            // 编辑显示类目
            Route::get('brands/{brand}/edit', 'Admin\Product\ProductBrandController@edit')->name('product.brands.edit');
            // 编辑品牌数据提交
            Route::patch('brands/{brand}', 'Admin\Product\ProductBrandController@update')->name('product.brands.update');
            // 删除品牌
            Route::delete('brands/{brand}', 'Admin\Product\ProductBrandController@destroy')->name('product.brands.destroy');

            // 商品列表
            Route::get('spus', 'Admin\Product\ProductSpuController@index')->name('product.spus.index');
            // 添加主商品
            Route::post('spus', 'Admin\Product\ProductSpuController@store')->name('product.spus.store');
            // 编辑显示主商品
            Route::get('spus/{spu}/edit', 'Admin\Product\ProductSpuController@edit')->name('product.spus.edit');
            // 编辑主商品数据提交
            Route::patch('spus/{spu}', 'Admin\Product\ProductSpuController@update')->name('product.spus.update');
            // 商品详情
            Route::get('spus/{spu}', 'Admin\Product\ProductSpuController@show')->name('product.spus.show');
            // 商品更新描述信息
            Route::put('spus/{spu}/description', 'Admin\Product\ProductSpuController@modifyDescription')->name('product.spus.modifyDescription');
            // 获取 sku 数据模板
            Route::get('spus/{spu}/getSkusTemplate', 'Admin\Product\ProductSpuController@getSkusTemplate')->name('product.spus.getSkusTemplate');
            // 添加 「属性-属性选项值」 或者 更新 「属性-属性选项值」
            Route::post('spus/{spu}/attrOptUpdate', 'Admin\Product\ProductSpuController@attrOptStoreOrUpdate')->name('product.spus.attrOptStoreOrUpdate');
            // 添加 sku 数据 或者 更新 sku 数据
            Route::post('spus/{spu}/skus', 'Admin\Product\ProductSpuController@skuStoreOrUpdate')->name('product.spus.skuStoreOrUpdate');
        });

    });
});

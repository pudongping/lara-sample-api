## 项目概述
- 项目名称：lara-sample-api
- 项目简介：  
这是一个基于 laravel6.x 开发的商城 api 接口，里面已经完成了大部分接口，但因门户接口和管理后台接口没有分离，在同个项目下，后续接口
太多，很容易导致接口冗乱，因此故将此项目拆分成了 `lara-shop-api` 门户接口和 `lara-shop-cms` 后台管理接口两个项目。目前此项目
**暂且不打算维护了** 更多功能请移步 `lara-shop-api` 门户接口和 `lara-shop-cms` 后台管理接口两个项目。当然你也可以继续使用此项目
功能都是好的，唯一的缺点是门户接口和管理接口在同一项目下，没有拆分。

## 关于分支

> 如果不需要已经写好的功能模块，那么可以直接切换到 `base-api-function` 分支，这个分支中只保留了基础 api 架构方法。通过这个分支，你可以快速的搭建适合于业务场景的 api 服务。
> `master` 分支中所以的功能均是基于 `base-api-function` 分支的基础架构方法所开发的。（使用了 `base-api-function` 分支，但是不知道这些基础方法如何使用，完全可以参考 `master` 分支中的写法）

## 功能如下
- 用户认证 —— 基于 jwt 认证登录、注册、登出、找回密码
- 图片验证码、短信验证码
- 支持多 guard （目前门户为：api、后台管理为：admin）
- 前后台用户支持多种普通认证形式 —— 账号、手机号、邮箱
- 前台用户第三方登录目前支持 —— 微信、微博（后续若需要支持其他第三方登录，只需要下载安装包即可，代码已经做了兼容处理）
- 个人中心 —— 用户个人中心，编辑资料
- 资源管理（上传图片） —— 修改头像时上传图片
- 基于 RBAC 的权限控制 —— 用户，角色，权限，路由
- 抽奖算法（支持大转盘、九宫格、刮刮乐）
- SPU —— 多品牌、多类目（类目树形结构）、收藏商品、购物车
- 多规格 SKU
- 图片资源系统
- 系统设置
- 更多功能 …… 你可以直接通过查看 `routes/api.php` 路由文件中了解，均有详细的注释信息

## 后端扩展包使用情况

扩展包 | 简介描述 | 本项目应用场景
--- | --- | --- 
[barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper) | 能让你的 IDE (PHPStorm, Sublime) 实现自动补全、代码智能提示和代码跟踪等功能 | 代码补全和智能提示
[barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) | 页面调试工具栏 (对 phpdebugbar 的封装) | 开发环境中的 DEBUG
[cyvelnet/laravel5-fractal ^2.3](https://packalyst.com/packages/package/cyvelnet/laravel5-fractal) | 比较好用的 transformer | 模型数据转换层
[gregwar/captcha](https://github.com/Gregwar/Captcha) | 图片验证码 | 图片验证码
[socialiteproviders/weixin](https://socialiteproviders.netlify.com/providers/weixin.html) | 微信登录管理包 | 微信网页授权登录
[socialiteproviders/weibo](https://socialiteproviders.netlify.com/providers/weibo.html) | 微博登录管理包 | 微博授权登录
[tymon/jwt-auth:1.0.0-rc.5](https://jwt-auth.readthedocs.io/en/develop/quick-start/) | jwt-auth 授权 | api 授权登录，需要执行 php artisan jwt:secret 以便生成 JWT_SECRET
[composer require overtrue/laravel-query-logger --dev](https://github.com/overtrue/laravel-query-logger) | 查询日志组件 | 记录每次 sql 查询日志
[spatie/laravel-permission](https://github.com/spatie/laravel-permission) | 角色权限管理 | 角色和权限控制

## 关于文档
### 接口文档采用 postman 编写

接口文档资料位于 `/doc/lara-api.postman_collection-V1.json` 采用 postman v1 版格式导出（需注意，目前 postman 支持 `Collection v1 (deprecated)` 、`Collection v2` 、`Collection v2.1 (recommended)` 三种版本）

![接口](https://upload-images.jianshu.io/upload_images/14623749-3c0a8bc291c7dbf1.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

**关于登录的接口，**，因为兼容了多种情况的登录（前端授权登录、后端授权登录、手机号登录、用户名登录……）相对来说，调用登录相关的接口会比较复杂，因此这里提供了专门的 `doc/login-doc.md` 文档，用于配合前端调用

`doc/image/cms-sku` 文件夹中有后台管理平台如何设计多规格的 `sku` 原型图    

`doc/image/数据库-思维脑图` 文件夹中有相关的数据库设计脑图

## 安装

1. 克隆源代码

克隆 `lara-sample-api` 源代码到本地：

```
// gitee
git clone https://gitee.com/pudongping/lara-sample-api.git

// github
git clone https://github.com/pudongping/lara-sample-api.git
```

2. 安装扩展包依赖

```
// 先切换到 lara-sample-api 项目根目录
cd lara-sample-api

// 执行安装命令
composer install
```

3. 生成配置文件

```
cp .env.example .env
```

你可以根据情况修改 .env 文件里的内容，如数据库连接、缓存、邮件设置、第三方授权登录等：

```

DB_HOST=localhost
DB_DATABASE=lara-sample-api
DB_USERNAME=homestead
DB_PASSWORD=secret

```

4. 生成数据表及生成测试数据

```
// 需要生成测试数据则执行：
php artisan migrate --seed

// 不需要生成测试数据则执行：
php artisan migrate
```

5. 生成秘钥

```

php artisan key:generate

php artisan jwt:secret

```

6. 创建 storage 软连接

```

php artisan storage:link

```

7. 赋予 storage 相应权限

```

// 建议在 Linux 系统中新建一个 www 用户，并设置该用户不可登录系统
useradd -s /sbin/nologin www

// 将项目目录所有权赋予 www 用户
chown -Rf www:www larablog 或者执行 setfacl -Rm u:www:rw lara-sample-api

// 给 storage 目录赋权限
chmod -Rf 0755 lara-sample-api/storage/

```

8.  配置 hosts 文件  （如果直接想部署在线上环境，则跳过此步骤）

如果开发环境没有采用 Laravel Homestead 则 ip 映射以你实际为主，一般为 127.0.0.1。我这里使用的 Laravel Homestead 虚拟机的 ip 地址为：192.168.10.10

```
// Linux 或 MacOS 环境

echo "192.168.10.10   lara-sample-api.test" | sudo tee -a /etc/hosts

// Windows 环境
需要打开 C:/Windows/System32/Drivers/etc/hosts  文件，然后新增一行

192.168.10.10 lara-sample-api.test
```

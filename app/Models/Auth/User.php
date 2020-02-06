<?php

namespace App\Models\Auth;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // 管理员 id
    const ADMIN_ID = 1;
    // 系统管理员 id
    const SYSADMIN_ID = 10000;

    const NORMAL_LOGIN = 0;
    const SOCIAL_WEIXIN = 1;

    /**
     * 登录方式（全部为小写）
     *
     * @var array
     */
    public static $loginType = [
        self::NORMAL_LOGIN => 'normal',
        self::SOCIAL_WEIXIN => 'weixin',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password',  'bound_id', 'social_type', 'openid',
        'unionid', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'avatar', 'headimgurl',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

<?php

namespace App\Models\Auth;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // 管理员 id
    const ADMIN_ID = 1;
    // 系统管理员 id
    const SYSADMIN_ID = 10000;

    const NORMAL_LOGIN = 0;
    const SOCIAL_WEIXIN = 1;
    const SOCIAL_WEIBO = 2;

    /**
     * 登录方式（全部为小写）
     *
     * @var array
     */
    public static $loginType = [
        self::NORMAL_LOGIN => 'normal',
        self::SOCIAL_WEIXIN => 'weixin',
        self::SOCIAL_WEIBO => 'weibo',
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
        'password', 'remember_token', 'openid', 'unionid'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * JWTSubject 对象的接口用于获取当前用户的 id
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWTSubject 对象的接口用于额外在 JWT 载荷中增加的自定义内容
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getSocialTypeAttribute($value)
    {
        return static::$loginType[$value];
    }

}

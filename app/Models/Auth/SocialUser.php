<?php

namespace App\Models\Auth;

use App\Models\Model;

class  SocialUser extends Model
{

    const SOCIAL_WEIXIN = 1;
    const SOCIAL_WEIBO = 2;
    const SOCIAL_QYWEIXIN = 3;

    /**
     * 登录方式（全部为小写）
     *
     * @var array
     */
    public static $loginType = [
        self::SOCIAL_WEIXIN => 'weixin',
        self::SOCIAL_WEIBO => 'weibo',
        self::SOCIAL_QYWEIXIN => 'qyweixin',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'social_type', 'openid', 'unionid', 'nickname', 'sex', 'language', 'city',
        'province', 'country', 'headimgurl', 'extra', 'name', 'phone', 'email'
    ];

    public function getSocialTypeAttribute($value)
    {
        return static::$loginType[$value] ?? static::$loginType[static::NORMAL_LOGIN];
    }

}

<?php
/**
 * 后台管理员
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/11
 * Time: 12:33
 */

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use SoftDeletes;

    // 管理员 id
    const ADMIN_ID = 1;
    // 系统管理员 id
    const SYSADMIN_ID = 10000;

    const STATE_NORMAL = 1;  // 正常
    const STATE_DENY = 0;  // 禁用

    /**
     * 管理员状态
     *
     * @var array
     */
    public static $state = [
        self::STATE_NORMAL => '正常',
        self::STATE_DENY => '禁用'
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'state'
    ];

    protected $hidden = [
        'password'
    ];

    public function getStateAttribute($value)
    {
        return self::$state[$value] ?? self::$state[self::STATE_NORMAL];
    }

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

}

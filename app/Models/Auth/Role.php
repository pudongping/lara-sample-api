<?php
/**
 * @link https://learnku.com/articles/19477  Laravel-permission 中文翻译
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:06
 */

namespace App\Models\Auth;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    protected $guard_name = 'admin';

    /**
     * 默认角色
     */
    const DEFAULT_ROLES = ['Administrator'];

    /**
     * 「守卫」本地作用域
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentGuard($query)
    {
        return $query->where('guard_name', config('api.default_guard_name'));
    }

}

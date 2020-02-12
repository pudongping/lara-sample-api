<?php

namespace App\Policies\Auth;

use App\Models\Auth\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 用户授权策略
     * 1、我们并不需要检查 $currentAdmin 是不是 null。未登录用户，框架会自动为其 「所有权限」 返回 false
     * 2、调用时，默认情况下，我们 不需要 传递当前登录用户至该方法内，因为框架会自动加载当前登录用户
     *
     * @param Admin $currentAdmin  当前登录用户实例
     * @param Admin $admin  要进行授权的用户实例
     * @return bool
     */
    public function updatePolicy(Admin $currentAdmin, Admin $admin)
    {
        // 如果是管理员，则直接跳过权限验证
        if (Admin::ADMIN_ID === intval($currentAdmin->id)) {
            return true;
        }
        return $currentAdmin->id === $admin->id;  // 其他的用户只允许自己修改自己的个人信息
    }

}

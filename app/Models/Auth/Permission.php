<?php
/**
 * @link https://learnku.com/articles/19477 Laravel-permission 中文翻译
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:04
 */

namespace App\Models\Auth;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{

    /**
     * 默认权限，不允许删除
     */
    const DEFAULT_PERMISSIONS = [
        'manage_setting',
        'manage_contents',
        'manage_users'
    ];

}

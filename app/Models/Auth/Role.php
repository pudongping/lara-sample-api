<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:06
 */

namespace App\Models\Auth;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    /**
     * 默认角色
     */
    const DEFAULT_ROLES = ['Administrator'];

}

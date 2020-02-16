<?php
/**
 * 检查路由菜单权限
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/16
 * Time: 17:54
 */

namespace App\Http\Middleware;

use Closure;
use App\Models\Auth\Admin;
use App\Support\Code;
use App\Exceptions\ApiException;

class CheckAdminMenusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $adminCount = Admin::all('id')->count();
        //  当前只有一个用户时或当前用户为超级管理员时，直接跳过权限
        if ((1 === $adminCount) || (Admin::ADMIN_ID === auth('admin')->id())) {
            return $next($request);
        }

        // 当前请求的 url 的别名，比如：admin.menus.index
        $currentRouteName = \Route::currentRouteName();

        // 如果当前请求的 url 没有设置路由名称，则直接跳过
        if (!$currentRouteName || ('admin.' == $currentRouteName)) {
            return $next($request);
        }

        // 查询当前请求的路由是否设置了权限
        $menuPermission = \DB::table('menus')->where('route_name', $currentRouteName)->value('permission');
        // 如果当前路由没有设置权限，则直接跳过
        if (!$menuPermission) {
            return $next($request);
        }

        // 当前路由需要判断的权限数组
        $permissionArr = explode('|', $menuPermission);
        // 当前用户拥有的所有权限
        $adminPermission = auth('admin')->user()->getAllPermissions()->pluck('name')->toArray();
        if (empty($adminPermission)) {  // 当前用户没有任何权限时
            throw new ApiException(Code::ERR_HTTP_FORBIDDEN);
        }

        $vkAdminPermission = array_flip($adminPermission);
        foreach ($permissionArr as $k => $permission) {
            if (isset($vkAdminPermission[$permission])) {
                // 去除公共权限（路由需要判断的权限和用户所有的权限重叠部份）
                unset($permissionArr[$k]);
            }
        }
        // 如果此时的路由权限数组为空，则当前用户具有操作当前路由的所有权限
        if (!empty($permissionArr)) {
            throw new ApiException(Code::ERR_HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

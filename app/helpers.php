<?php
/**
 * 自定义助手函数
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/2
 * Time: 0:45
 */

if (! function_exists('getCurrentAction')) {
    /**
     * 获取当前路由的控制器名称和方法名称
     *
     * @return array
     */
    function getCurrentAction()
    {
        $action = Route::current()->getActionName();
        if (!strstr($action, '@')) {
            // 防止路由中采用匿名函数返回数据
            return ['controller' => false, 'method' => false];
        }
        list($controller, $method) = explode('@', $action);
        return compact('controller', 'method');
    }
}

if (! function_exists('user_log')) {
    /**
     * 用户操作日志
     *
     * @param null $msg
     */
    function user_log($msg = null)
    {
        $user = Auth::user();

        if (empty($user)) {
            $uid = \App\Models\Auth\User::SYSADMIN_ID;
        } else {
            $uid = $user->id;
        }

        $log = new \App\Models\Admin\Setting\Log();
        $log->user_id = $uid;
        $log->client_ip = request()->ip();
        // JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES = 256 + 64 = 320
        $log->header = json_encode(request()->header(), 320);
        $log->description = $msg;
        $log->save();
    }
}

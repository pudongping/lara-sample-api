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
     * 管理员操作日志
     *
     * @param null $msg
     */
    function user_log($msg = null)
    {
        $user = Auth::guard(Auth::getDefaultDriver())->user();

        if (empty($user)) {
            $uid = \App\Models\Auth\Admin::SYSADMIN_ID;
        } else {
            $uid = $user->id;
        }

        $log = new \App\Models\Admin\Setting\Log();
        $log->user_id = $uid;
        $log->client_ip = request()->ip();
        $log->guard_name = Auth::getDefaultDriver();
        // JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES = 256 + 64 = 320
        $log->header = json_encode(request()->header(), 320);
        $log->description = $msg;
        $log->save();
    }
}

if (! function_exists('validateChinaPhoneNumber')) {
    /**
     * 验证中国手机号码是否合法
     *
     * @param string $number
     * @return bool
     */
    function validateChinaPhoneNumber(string $number): bool
    {
        return preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/', $number);
    }
}

if (! function_exists('validateUserName')) {
    /**
     * 验证用户名是否合法
     *
     * @param string $username
     * @return bool
     */
    function validateUserName(string $username): bool
    {
        return preg_match('/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/', $username);
    }
}

if (! function_exists('fetchAccountField')) {
    /**
     * 根据账号的值获取账号字段
     *
     * @param string $login
     * @param string $defaultField
     * @return string
     */
    function fetchAccountField(string $login, string $defaultField = 'name'): string
    {
        $map = [
            'email' => filter_var($login, FILTER_VALIDATE_EMAIL),
            'phone' => validateChinaPhoneNumber($login),
            'name' => validateUserName($login)
        ];
        foreach ($map as $field => $value) {
            if ($value) return $field;
        }
        return $defaultField;
    }
}

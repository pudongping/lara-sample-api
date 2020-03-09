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

if (! function_exists('batchUpdate')) {
    /**
     * $where = [ 'id' => [180, 181, 182, 183], 'user_id' => [5, 15, 11, 1]];
     * $needUpdateFields = [ 'view_count' => [11, 22, 33, 44], 'updated_at' => ['2019-11-06 06:44:58', '2019-11-30 19:59:34', '2019-11-05 11:58:41', '2019-12-13 01:27:59']];
     *
     * 最终执行的 sql 语句如下所示
     *
     * UPDATE articles SET
     * view_count = CASE
     * WHEN id = 183 AND user_id = 1 THEN 44
     * WHEN id = 182 AND user_id = 11 THEN 33
     * WHEN id = 181 AND user_id = 15 THEN 22
     * WHEN id = 180 AND user_id = 5 THEN 11
     * ELSE view_count END,
     * updated_at = CASE
     * WHEN id = 183 AND user_id = 1 THEN '2019-12-13 01:27:59'
     * WHEN id = 182 AND user_id = 11 THEN '2019-11-05 11:58:41'
     * WHEN id = 181 AND user_id = 15 THEN '2019-11-30 19:59:34'
     * WHEN id = 180 AND user_id = 5 THEN '2019-11-06 06:44:58'
     * ELSE updated_at END
     *
     *
     * 批量更新数据
     *
     * @param string $tableName  需要更新的表名称
     * @param array $where  需要更新的条件
     * @param array $needUpdateFields  需要更新的字段
     * @return bool|int  更新数据的条数
     */
    function batchUpdate(string $tableName, array $where, array $needUpdateFields)
    {

        if (empty($where) || empty($needUpdateFields)) return false;
        // 第一个条件数组的值
        $firstWhere = $where[array_key_first($where)];
        // 第一个条件数组的值的总数量
        $whereFirstValCount = count($firstWhere);
        // 需要更新的第一个字段的值的总数量
        $needUpdateFieldsValCount = count($needUpdateFields[array_key_first($needUpdateFields)]);
        if ($whereFirstValCount !== $needUpdateFieldsValCount) return false;
        // 所有的条件字段数组
        $whereKeys = array_keys($where);

        // 绑定参数
        $building = [];

//        $whereArr = [
//          0 => "id = 180 AND ",
//          1 => "user_id = 5 AND ",
//          2 => "id = 181 AND ",
//          3 => "user_id = 15 AND ",
//          4 => "id = 182 AND ",
//          5 => "user_id = 11 AND ",
//          6 => "id = 183 AND ",
//          7 => "user_id = 1 AND ",
//        ]
        $whereArr = [];
        $whereBuilding = [];
        foreach ($firstWhere as $k => $v) {
            foreach ($whereKeys as $whereKey) {
//                $whereArr[] = "{$whereKey} = {$where[$whereKey][$k]} AND ";
                $whereArr[] = "{$whereKey} = ? AND ";
                $whereBuilding[] = $where[$whereKey][$k];
            }
        }

//        $whereArray = [
//            0 => "id = 180 AND user_id = 5",
//            1 => "id = 181 AND user_id = 15",
//            2 => "id = 182 AND user_id = 11",
//            3 => "id = 183 AND user_id = 1",
//        ]
        $whereArrChunck = array_chunk($whereArr, count($whereKeys));
        $whereBuildingChunck = array_chunk($whereBuilding, count($whereKeys));

        $whereArray = [];
        foreach ($whereArrChunck as $val) {
            $valStr = '';
            foreach ($val as $vv) {
                $valStr .= $vv;
            }
            // 去除掉后面的 AND 字符及空格
            $whereArray[] = rtrim($valStr, "AND ");
        }

        // 需要更新的字段数组
        $needUpdateFieldsKeys = array_keys($needUpdateFields);

        // 拼接 sql 语句
        $sqlStr = '';
        foreach ($needUpdateFieldsKeys as $needUpdateFieldsKey) {
            $str = '';
            foreach ($whereArray as $kk => $vv) {
//                $str .= ' WHEN ' . $vv . ' THEN ' . $needUpdateFields[$needUpdateFieldsKey][$kk];
                $str .= ' WHEN ' . $vv . ' THEN ? ';
                // 合并需要绑定的参数
                $building[] = array_merge($whereBuildingChunck[$kk], [$needUpdateFields[$needUpdateFieldsKey][$kk]]);
            }
            $sqlStr .= $needUpdateFieldsKey . ' = CASE ' . $str . ' ELSE ' . $needUpdateFieldsKey . ' END, ';
        }

        // 去除掉后面的逗号及空格
        $sqlStr = rtrim($sqlStr, ', ');

        $tblSql = 'UPDATE ' . $tableName . ' SET ';

        $tblSql = $tblSql . $sqlStr;

        $building = array_reduce($building,"array_merge",array());
//        return [$tblSql, $building];
        return \DB::update($tblSql, $building);
    }
}

if (! function_exists('http_get')) {
    /**
     * HTTP Get 请求数据
     *
     * @param $api  string  需要请求的 url
     * @param $query  array  请求参数数组
     * @return mixed
     */
    function http_get($api, $args)
    {
        $client = new \GuzzleHttp\Client;
        $query = http_build_query($args);
        $response = $client->get($api . '?' . $query);
        $result = json_decode($response->getBody(), true);
        return $result;
    }
}

if (! function_exists('makeTreeData')) {
    /**
     * 对数据进行树型结构处理
     *
     * @param array $data  需要处理的数据 二维数组
     * @param int $root  顶级数据的标识
     * @param int $level  需要几层子级（设置足够大的数字，意味着需要无限级）
     * @param array $column   父级字段名、数据本身字段名、子级字段名
     * @return array
     */
    function makeTreeData(array $data, $root = 0, $level = 1000, $column = ['parent_column' => 'pid', 'children_column' => 'id', 'grandson_column' => 'children'])
    {
        $tree = [];
        $parentColumn = $column['parent_column'];
        $childrenColumn = $column['children_column'];
        $grandsonColumn = $column['grandson_column'];
        foreach ($data as $item) {
            if ($root === (int)$item[$parentColumn]) {
                if ($level > 0) {
                    $item[$grandsonColumn] = makeTreeData($data, $item[$childrenColumn], $level-1);
                }
                // 顶级
                $tree[] = $item;
            }
        }
        --$level;
        return $tree;
    }
}

if (! function_exists('img_with_base_url')) {
    /**
     * 使图片的相对路径成为网址绝对路径
     *
     * @param $imgPath
     * @return string
     */
    function img_with_base_url($imgPath)
    {
        if (empty($imgPath)) return '';
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($imgPath, ['http://', 'https://'])) {
            return $imgPath;
        }
        return config('app.url') . $imgPath;
    }
}

if (! function_exists('str_explode')) {
    /**
     * 用指定分隔符切割字符串
     *
     * @param string $str  需要切割的字符串
     * @param string $delimiter  分隔符
     * @return array  以分隔符切割的数组
     */
    function str_explode(string $str, string $delimiter) : array
    {
        // array_filter 将数组中的空值移除
        return array_filter(explode($delimiter, trim($str, $delimiter)));
    }
}

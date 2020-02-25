<?php
/**
 * Error code
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/2
 * Time: 13:56
 */

namespace App\Support;


class Code
{

    /**
     * http 正常时,返回码
     */
    const SUCC_HTTP_OK = 200;
    const SUCC_HTTP_CREATED = 201;
    const SUCC_HTTP_ACCEPTED = 202;
    const SUCC_HTTP_NO_CONTENT = 204;

    /**
     * http 相关错误
     */
    const ERR_HTTP_BAD_REQUEST = 400;
    const ERR_HTTP_UNAUTHORIZED = 401;
    const ERR_HTTP_FORBIDDEN = 403;
    const ERR_HTTP_NOT_FOUND = 404;
    const ERR_HTTP_METHOD_NOT_ALLOWED = 405;
    const ERR_HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const ERR_HTTP_UNPROCESSABLE_ENTITY = 422;
    const ERR_HTTP_TOO_MANY_REQUESTS = 429;
    const ERR_HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * 10000 系统级别错误
     */
    const ERR_QUERY = 10001;
    const ERR_DB = 10002;
    const ERR_PARAMS = 10003;
    const ERR_MODEL = 10004;
    const ERR_FILE_UP_LOAD = 10005;
    const ERR_PERM = 10006;
    const ERR_EXCEL_COLUMN = 10007;

    /**
     * 20000 服务级别错误
     */
    const ERR_MENU_FIELD = 20001;
    const ERR_EXPORT = 20002;
    const ERR_QRCODE = 20003;
    const ERR_USER_EXIST = 20004;
    const ERR_MODEL_EXIST = 20005;
    const ERR_NEED_BOUND = 20006;


    public static $msgs = [
        self::SUCC_HTTP_OK => '操作成功',
        self::SUCC_HTTP_CREATED => '新资源： %s 创建成功',
        self::SUCC_HTTP_ACCEPTED => '服务器暂未处理',
        self::SUCC_HTTP_NO_CONTENT => '成功删除资源',

        self::ERR_HTTP_BAD_REQUEST => '请求异常',
        self::ERR_HTTP_UNAUTHORIZED => '登录已过期，请重新登录',
        self::ERR_HTTP_FORBIDDEN => '无权访问该地址',
        self::ERR_HTTP_NOT_FOUND => '请求地址不存在',
        self::ERR_HTTP_METHOD_NOT_ALLOWED => '不允许请求该方法',
        self::ERR_HTTP_UNSUPPORTED_MEDIA_TYPE => '请求体类型错误',
        self::ERR_HTTP_UNPROCESSABLE_ENTITY => '参数校验错误',
        self::ERR_HTTP_TOO_MANY_REQUESTS => '请求频次达到上限',
        self::ERR_HTTP_INTERNAL_SERVER_ERROR => '服务器内部错误',

        self::ERR_QUERY => '数据库操作失败',
        self::ERR_DB => '数据库连接失败',
        self::ERR_PARAMS => '参数验证失败： %s',
        self::ERR_MODEL => '数据不存在',
        self::ERR_FILE_UP_LOAD => '文件上传出错',
        self::ERR_PERM => '没有该操作权限，请联系管理员',
        self::ERR_EXCEL_COLUMN => 'Excel文件列数异常',

        self::ERR_MENU_FIELD => '该菜单存在子菜单，无法删除',
        self::ERR_EXPORT => '导出文件失败，请联系管理员',
        self::ERR_QRCODE => '二维码生成错误',
        self::ERR_USER_EXIST => '账号已存在',
        self::ERR_MODEL_EXIST => '%s 数据已存在',
        self::ERR_NEED_BOUND => '%s未绑定',
    ];

    /**
     * 提示代码
     * @var | int
     */
    protected static $code;

    /**
     * 提示信息
     * @var | string
     */
    protected static $msg;

    /**
     * 详情信息
     * @var
     */
    protected static $detail;

    /**
     * 设置提示信息
     *
     * @param $code 提示代码
     * @param null $msg 提示信息
     * @param array $params 提示信息中动态参数
     */
    public static function setCode($code, $msg = null, array $params = [])
    {
        self::$code = $code = (int)$code;
        if (null == $msg) {
            if (isset(self::$msgs[$code])) {
                if (!empty($params)) {
                    array_unshift($params, self::$msgs[$code]);
                    self::$msg = call_user_func_array('sprintf', $params);
                } else {
                    self::$msg = self::$msgs[$code];
                }
            } else {
                self::$msg = '提示信息未定义';
            }
        } else {
            self::$msg = $msg;
        }

        if (self::SUCC_HTTP_OK !== $code) {
            // save log
        }

    }

    /**
     * 获取提示信息，带错误码
     *
     * @return array 提示代码，提示信息
     */
    public static function getCode()
    {
        if (is_null(self::$code)) {
            self::setCode(self::SUCC_HTTP_OK);
        }
        return [self::$code, self::$msg];
    }

    /**
     * 获取提示信息，不带错误码
     *
     * @return mixed
     */
    public static function getErrMsg()
    {
        return self::$msg;
    }

    /**
     * 设置详细信息
     *
     * @param $detail
     */
    public static function setDetail($detail)
    {
        self::$detail = $detail;
    }

    /**
     * 获取详细信息
     *
     * @return mixed
     */
    public static function getDetail()
    {
        return self::$detail;
    }

}

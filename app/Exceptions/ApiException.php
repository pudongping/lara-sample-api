<?php
/**
 * 自定义异常处理
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019/2/2
 * Time: 16:51
 */

namespace App\Exceptions;

use Exception;
use App\Support\Code;
use Throwable;

class ApiException extends Exception
{
    public function __construct(int $code = 0, $params = null, $message = null)
    {
        Code::setCode($code, $message, $params);
        list($code, $msg) = Code::getCode();
        parent::__construct('api exception: ' . $msg);
    }
}

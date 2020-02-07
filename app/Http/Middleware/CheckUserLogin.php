<?php
/**
 * 检查用户是否已经登录
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/7
 * Time: 12:03
 */

namespace App\Http\Middleware;

use App\Support\Code;
use Closure;
use App\Exceptions\ApiException;

class CheckUserLogin
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
         if (!\Auth::guard('api')->check()) {
             throw new ApiException(Code::ERR_HTTP_UNAUTHORIZED);
         }
        return $next($request);
    }

}

<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\ApiException;
use App\Support\Code;

class RefreshTokenMiddleware
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
        $response = $next($request);

        $defaultPer = intval(config('api.custom_jwt.auto_refresh_time'));  // 距离过期时间多少秒后去刷新 token

        try {
            $exp = auth()->payload()->get('exp');  // 获取当前 token 的过期时间
        } catch (\Exception $exception) {
            throw new ApiException(Code::ERR_HTTP_UNAUTHORIZED);
        }

        $leadTime =  $exp - time();  // 过期时间减去当前时间为时间差

        if ($leadTime <= $defaultPer) {
            // 当时间差小于或者等于约定的时间时，主动去刷新 token
            $userRespository = app('App\Repositories\Auth\UserRepository');
            // 这里缓存一下 access_token 的原因是防止并发请求下，维护 token 的黑名单过大
            // 因为 token 在有效期的时候，再次去刷新 token （以旧 token 去换取新 token）但是此时旧 token 还没有过期，仍然是可以用的，为了减少颁发的 token 个数，则存一下缓存
            // https://learnku.com/articles/17883
            $refreshToken = \Cache::remember('auto_refresh_key', 60, function () use ($userRespository) {
                return $userRespository->refreshToken()['access_token'];
            });
            // 遵循下 http 请求头命名规范
            $response->header('Refresh-Token', $refreshToken);
        }

        return $response;
    }
}

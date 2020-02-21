<?php
/**
 * 短信验证码
 * @link https://github.com/overtrue/easy-sms
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/21
 * Time: 11:01
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerificationCodeRequest;
use Overtrue\EasySms\EasySms;
use App\Support\Code;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use App\Exceptions\ApiException;

class VerificationCodesController extends Controller
{

    public function __construct()
    {
        $this->init();
    }

    /**
     * 发送短信验证码
     *
     * @param VerificationCodeRequest $request
     * @param EasySms $easySms
     * @return mixed
     * @throws ApiException
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $phone = $request->phone;

        if (app()->environment('production')) {
            // 生成 6 位随机数，左侧补 0
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
            try {
                $easySms->send($phone, [
                    'template' => config('easysms.gateways.aliyun.templates.register'),
                    'data' => [
                        'code' => $code
                    ],
                ]);
            } catch (NoGatewayAvailableException $exception) {
                $message = $exception->getException('aliyun')->getMessage();
                throw new ApiException(Code::ERR_HTTP_INTERNAL_SERVER_ERROR, [], $message);
            }
        } else {
            $code = '123456';
        }

        $key = config('api.cache_key.verificationCode') . \Str::random(15);
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5 分钟过期
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        $result = [
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ];

        return $this->response->send($result);
    }

}

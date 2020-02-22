<?php
/**
 * 图片验证码
 * @link https://github.com/Gregwar/Captcha
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/5
 * Time: 13:44
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends Controller
{

    public function __construct()
    {
        $this->init();
    }

    /**
     * 生成图片验证码
     *
     * @param CaptchaBuilder $captchaBuilder
     * @return mixed
     */
    public function store(CaptchaBuilder $captchaBuilder)
    {
        $key = config('api.cache_key.captcha') . \Str::random(15);
        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinute(2);
        // 缓存 5 个字节的图片验证码，不区分大小写
        \Cache::put($key, ['captcha_code' => strtolower($captcha->getPhrase())], $expiredAt);
        $result = [
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline()  // 默认生成 w => 150px, h => 40px，base64
        ];

        return $this->response->send($result);
    }

}

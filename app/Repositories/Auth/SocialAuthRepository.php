<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/21
 * Time: 14:38
 */

namespace App\Repositories\Auth;

use App\Exceptions\ApiException;
use App\Support\Code;


class SocialAuthRepository
{

    /**
     * 企业微信授权
     *
     * @param $code
     * @return array
     * @throws ApiException
     */
    public function qywxUser($code)
    {
        try {
            $accessToken = $this->getAccessToken();
            $userId = $this->getUserId($accessToken, $code);
            $fullUserInfo = $this->getUserInfo($accessToken, $userId);
            return $this->simplyUserInfo($fullUserInfo);
        } catch (\Exception $exception) {
            throw new ApiException(Code::ERR_PARAMS, ['参数错误，未获取用户信息']);
        }
    }

    /**
     * 第一步：获取 access_token
     * @link https://work.weixin.qq.com/api/doc/90000/90135/91039
     *
     * @return mixed
     */
    public function  getAccessToken()
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
        $args = [
            'corpid' => config('api.social_auth.qyweixin.corpid'),
            'corpsecret' => config('api.social_auth.qyweixin.corpsecret'),
        ];

        $accessToken = \Cache::remember(config('api.cache_key.qywx_access_token'), 7000, function () use ($url, $args) {
            return http_get($url, $args)['access_token'];
        });

        return $accessToken;
    }

    /**
     * 第二步：前端调用微信授权地址，获取 code
     * 第三步：用 access_token 和 code 换取用户唯一标识，如果在企业中则有 UserId，不在则有 OpenId
     * @link https://work.weixin.qq.com/api/doc/90000/90135/91023
     *
     * @param $accessToken  企业微信的 access_token
     * @param $code  授权 code
     * @return mixed
     * @throws ApiException
     */
    public function getUserId($accessToken, $code)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo';
        $args = [
            'access_token' => $accessToken,
            'code' => $code,
        ];

//         a) 当用户为企业成员时返回示例如下
//        {
//            "errcode": 0,
//           "errmsg": "ok",
//           "UserId":"USERID",
//           "DeviceId":"DEVICEID"
//        }
//        b) 非企业成员授权时返回示例如下：
//        {
//            "errcode": 0,
//   "errmsg": "ok",
//   "OpenId":"OPENID",
//   "DeviceId":"DEVICEID"
//}

        $response = http_get($url, $args);

        if (!empty($response['errcode'])) {
            throw new ApiException(Code::ERR_PARAMS, [], '微信企业授权 code 已失效');
        }

        if (isset($response['OpenId'])) {  // 当前用户不在该企业微信中
            throw new ApiException(Code::ERR_PARAMS, [], '当前用户不在该企业微信组织中');
        }

        return $response['UserId'];
    }

    /**
     * 第四步：获取企业微信成员信息
     * @link https://work.weixin.qq.com/api/doc/90000/90135/90196
     *
     * @param $accessToken
     * @param $userId
     * @return mixed|\returns|string
     */
    public function getUserInfo($accessToken, $userId)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/get';
        $args = [
            'access_token' => $accessToken,
            'userid' => $userId,
        ];

        return http_get($url, $args);
    }

    /**
     * 精简企业微信成员信息
     *
     * @param $fullUserInfo
     * @return array
     */
    public function simplyUserInfo($fullUserInfo)
    {
        return [
            'openid' => $fullUserInfo['userid'],  // 当前授权的唯一标识
            'name' => $fullUserInfo['name'],
            'phone' => $fullUserInfo['mobile'],
            'sex' => $fullUserInfo['gender'],
            'email' => $fullUserInfo['email'],
            'headimgurl' => $fullUserInfo['avatar'],
            'department' => $fullUserInfo['department'],
            'position' => $fullUserInfo['position'],
        ];
    }

}

<?php
/**
 * 用户相关操作
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/4
 * Time: 20:48
 */

namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\Auth\User;
use App\Support\Code;
use App\Exceptions\ApiException;
use App\Models\Common\Image;

class UserRepository extends BaseRepository
{

    protected $model;
    protected $imageModel;

    public function __construct(
        User $user,
        Image $imageModel
    ) {
        $this->model = $user;
        $this->imageModel = $imageModel;
    }

    /**
     * 注册
     * 支持用户名「/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/」、中国手机号、邮箱三种账号方式
     *
     * @param $request
     * @return bool|mixed
     * @throws ApiException
     */
    public function register($request)
    {
        $captchaData = cache($request->captcha_key);
        if (!$captchaData) {
            Code::setCode(Code::ERR_PARAMS, null, ['图片验证码已失效']);
            return false;
        }

        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            // 输入的图片验证码错误则直接删除掉
            \Cache::forget($request->captcha_key);
            Code::setCode(Code::ERR_PARAMS, null, ['图片验证码错误']);
            return false;
        }

         $account = $request->account;
         $accountField = fetchAccountField($account);
         if ('name' === $accountField) {
             if (!validateUserName($account)) {
                 Code::setCode(Code::ERR_PARAMS, null, ['账号需以字母开头，可以包括字母、数字、下划线、横杠']);
                 return false;
             }
         }
         $item = $this->getSingleRecord($account, $accountField, false);
         if ($item) throw new ApiException(Code::ERR_USER_EXIST);

         $input = [
             $accountField => $account,
             'password' => bcrypt($request->password)
         ];
         $user = $this->store($input);

         return $user;
    }

    /**
     * 第三方授权登录
     *
     * @param $request
     * @return array|bool
     * @throws ApiException
     */
    public function socialStore($request)
    {
        $socialType = strtolower($request->socialType);
        $socialTypeCode = array_flip(User::$loginType)[$socialType] ?? User::NORMAL_LOGIN;
        // 去除掉 normal 方式
        $allowSocialTypeArr = \Arr::except(User::$loginType, [User::NORMAL_LOGIN]);

        if (!in_array($socialType, $allowSocialTypeArr)) {
            Code::setCode(Code::ERR_HTTP_NOT_FOUND);
            return false;
        }
        $driver = \Socialite::driver($socialType);  // SocialiteProviders\Weixin\Provider
        try {
            if ($code = $request->code) {  // 客户端只传 code 的情况
                $response = $driver->getAccessTokenResponse($code);
//                $response => [
//                    "access_token" => "30_UZ4_rRxAd94Hmb6JHNisCJlH2Az98OXGv51up_ASu--M32ejr2zpSPC2Hx90lh11pUe8thYSseeVkOfOmi3dtw",
//                    "expires_in" => 7200,
//                    "refresh_token" => "30_Pjf7A1G_cJVBXZNKmW3IKYfMn4TM97s6PFqIVwTBDl4IFZnoDRMeyF3ud22V_sDjRV6rw9XxmRQZiWhwMk8Ssg",
//                    "openid" => "oHt9G1HJqhNFbXPUJKZMEVig_678",
//                    "scope" => "snsapi_userinfo",
//                ]
                $accessToken = \Arr::get($response, 'access_token');
                // 当授权模式为 code 模式的时候，插件已经为我们设置了 openid
            } else {  // 客户端直接传了 access_token
                $accessToken = $request->access_token;
                // 微信授权登录的流程中换取用户信息的接口，需要同时提交 access_token 和 openid （只有微信授权时需要， 其他授权方式不需要）
                if (User::$loginType[User::SOCIAL_WEIXIN] == $socialType) {
                    $driver->setOpenId($request->openid);
                }
            }
            // 授权后获取的用户信息
            $oauthUser = $driver->userFromToken($accessToken);  // Laravel\Socialite\Two\AbstractProvider::class@userFromToken
        } catch (\Exception $e) {
            throw new ApiException(Code::ERR_PARAMS, ['参数错误，未获取用户信息']);
        }

        $user = null;
        $unionid = '';

        if (User::$loginType[User::SOCIAL_WEIXIN] === $socialType) {
            // 只有在用户将公众号绑定到微信开放平台帐号后，才会出现 unionid 字段
            // 获取微信 unionid  Laravel\Socialite\AbstractUser::class@offsetExists
            $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
            if ($unionid) {
                $user = User::where('unionid', $unionid)->where('social_type', $socialTypeCode)->first();
            }
        }

        if (!$user) {
            // 否则直接用 openid 去查询。$oauthUser->getId() 默认为 openid
            $user = User::where('openid', $oauthUser->getId())->where('social_type', $socialTypeCode)->first();
        }

        if (!$user) {  // 当前没有用户时则先创建用户
            $input = [
                'social_type' => $socialTypeCode,
                'nickname' => $oauthUser->getNickname(),   // Laravel\Socialite\AbstractUser::class@getNickname
                'headimgurl' => $oauthUser->getAvatar(),
                'openid' => $oauthUser->getId(),
                'unionid' => $unionid
            ];
            $user = $this->store($input);
        }

        $token = auth('api')->login($user);  // 会直接通过 jwt-auth 返回 token

        return $this->respondWithToken($token);
    }

    /**
     * 用户名/中国手机号/邮箱登录
     *
     * @param $request
     * @return array|bool
     * @throws ApiException
     */
    public function login($request)
    {
        $remeberMe = boolval($request->remember);
        $account = $request->account;
        $accountField = fetchAccountField($account);
        if ('name' === $accountField) {
            if (!validateUserName($account)) {
                Code::setCode(Code::ERR_PARAMS, null, ['账号需以字母开头，可以包括字母、数字、下划线、横杠']);
                return false;
            }
        }
        $credentials = [
            $accountField => $account,
            'password' => $request->password
        ];

        if ($remeberMe) {  // 勾选了「记住我」时
            $token = auth('api')->setTTL(config('api.custom_jwt.remember_me_ttl'))->attempt($credentials);
        } else {
            $token = auth('api')->attempt($credentials);
        }

        if (!$token) {
            throw new ApiException(Code::ERR_PARAMS, ['参数错误，未获取用户信息']);
        }

        return $this->respondWithToken($token);
    }

    /**
     * 修改登录用户信息
     *
     * @param $request
     * @return mixed
     */
    public function modify($request)
    {
        $input = $request->only(['name', 'email', 'phone']);
        if ($request->avatar_image_id) {
            $image = $this->imageModel->find($request->avatar_image_id);
            $input['avatar'] = $image->path;
        }

        $data = $this->update($request->user()->id, $input);

        return $data;
    }

    /**
     * 刷新 token
     *
     * @return array
     */
    public function refreshToken()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * 删除 token （退出登录）
     */
    public function logout()
    {
        auth('api')->logout();
    }

    /**
     * 获取令牌数组结构
     *
     * @param $token 令牌 token
     * @return array
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,  // 单位为秒，3600s
        ];
    }

}

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
use App\Repositories\Auth\SocialAuthRepository;
use App\Models\Auth\SocialUser;
use App\Http\Controllers\Auth\VerificationCodesController;

class UserRepository extends BaseRepository
{

    protected $socialType;  // 当前授权登录的类型

    protected $model;
    protected $imageModel;
    protected $socialAuthRepository;
    protected $socialUserModel;
    protected $verificationCodesController;

    public function __construct(
        User $user,
        Image $imageModel,
        SocialAuthRepository $socialAuthRepository,
        SocialUser $socialUserModel,
        VerificationCodesController $verificationCodesController
    ) {
        $this->model = $user;
        $this->imageModel = $imageModel;
        $this->socialAuthRepository = $socialAuthRepository;
        $this->socialUserModel = $socialUserModel;
        $this->verificationCodesController = $verificationCodesController;
    }

    /**
     * 手机号注册方式，第一步，检验图片验证码有效性
     *
     * @param $request
     * @return array|bool
     * @throws ApiException
     */
    public function checkRegister($request)
    {
        $captchaData = cache($request->captcha_key);
        if (!$captchaData) {
            Code::setCode(Code::ERR_PARAMS, null, ['图片验证码已失效']);
            return false;
        }

        if (!hash_equals($captchaData['captcha_code'], $request->captcha_code)) {
            // 输入的图片验证码错误则直接删除掉
            \Cache::forget($request->captcha_key);
            Code::setCode(Code::ERR_PARAMS, null, ['图片验证码错误']);
            return false;
        }

        // 发送短信
        $phoneCode = $this->verificationCodesController->sendSms($request->phone);

        $key = config('api.cache_key.checkRegister') . \Str::random(15);
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5 分钟过期
        \Cache::put($key, ['phone' => $request->phone, 'password' => $request->password, 'phone_code' => $phoneCode], $expiredAt);

        $result = [
            'register_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ];

        return $result;
    }

    /**
     * 手机号注册方式，第二步，保存注册信息并直接登录
     *
     * @param $request
     * @return array|bool
     * @throws \Exception
     */
    public function register($request)
    {
        $registerData = cache($request->register_key);
        if (!$registerData) {
            Code::setCode(Code::ERR_PARAMS, '注册信息已失效，请重新注册');
            return false;
        }

        if (!hash_equals($registerData['phone_code'], $request->phone_code)) {
            // 输入的短信验证码错误则直接删除掉
            \Cache::forget($request->register_key);
            Code::setCode(Code::ERR_PARAMS, '短信验证码错误');
            return false;
        }

        $input = [
            'phone' => $registerData['phone'],
            'password' => bcrypt($registerData['password'])
        ];
        $user = $this->store($input);

        $token = auth('api')->login($user);  // 会直接通过 jwt-auth 返回 token

        return $this->respondWithToken($token);
    }

    /**
     * 直接使用 openid 登录时，第一步，检查是否已经绑定了手机号，绑定了则直接登录
     * 通过检查 「social_users」 表中 user_id 是否不为 0，来判断是否已经绑定了手机号，存在 user_id 则一定绑定了手机号
     *
     * @param $request
     * @return array|bool
     */
    public function checkBoundPhone($request)
    {
        $socialType = strtolower($request->socialType);

        if (!in_array($socialType, SocialUser::$loginType)) {
            Code::setCode(Code::ERR_HTTP_NOT_FOUND);
            return false;
        }

        // 第三方授权登录标识 code
        $socialTypeCode = array_flip(SocialUser::$loginType)[$socialType];

        $socialUser = null;
        if (!is_null($request->unionid)) {
            $socialUser = SocialUser::where('unionid', $request->unionid)->where('social_type', $socialTypeCode)->first();
        }

        if (!$socialUser) {
            $socialUser = SocialUser::where('openid', $request->openid)->where('social_type', $socialTypeCode)->first();
        }

        if (empty($socialUser) || empty($socialUser->user_id)) {
            Code::setCode(Code::ERR_NEED_BOUND, null, ['手机号']);
            return false;
        }

        // 如果此时已经有了 user_id
        $user = User::find($socialUser->user_id);
        if (empty($user)) {
            Code::setCode(Code::ERR_MODEL, '主账号不存在或没有绑定手机号');
            return false;
        }

        $token = auth('api')->login($user);  // 会直接通过 jwt-auth 返回 token

        return $this->respondWithToken($token);
    }

    /**
     *
     * 直接使用 openid 登录时，第二步，如果此时是新账户则需要绑定手机号（需要先调用发送手机验证码的接口）
     * 授权在服务端时，需要绑定手机号的情况下
     *
     * @param $request
     * @return array|bool
     * @throws \Exception
     */
    public function socialLogin($request)
    {
        $socialType = strtolower($request->socialType);

        if (!in_array($socialType, SocialUser::$loginType)) {
            Code::setCode(Code::ERR_HTTP_NOT_FOUND);
            return false;
        }

        // 第三方授权登录标识 code
        $socialTypeCode = array_flip(SocialUser::$loginType)[$socialType];

        $phoneData = cache($request->phone_key);
        if (!$phoneData) {
            Code::setCode(Code::ERR_PARAMS, '短信验证码已失效');
            return false;
        }

        if (!hash_equals($phoneData['phone_code'], $request->phone_code)) {
            // 输入的短信验证码错误则直接删除掉
            \Cache::forget($request->phone_key);
            Code::setCode(Code::ERR_PARAMS, '短信验证码错误');
            return false;
        }

        // 此时为第三方登录在 服务端授权时。如果直接传 openid 给服务端则表示前端已经授权完毕了
        $socialUserId = null;
        if ($request->social_user_key) {
            $socialUserData = cache($request->social_user_key);
            if (!$socialUserData) {
                Code::setCode(Code::ERR_PARAMS, '授权登录失败，请重新授权');
                return false;
            }
            $socialUserId = $socialUserData['social_user_id'];  // 授权在服务端时，先保存到缓存中的 social_users 表的 id
        }

        \DB::beginTransaction();
        try {
            $user = $this->getSingleRecord($phoneData['phone'], 'phone', false);
            if (!$user) {
                // 「直接使用 openid 注册场景」 或 「授权登录在服务端授权，但是没有绑定手机号的场景」时
                $user = $this->store(['phone' => $phoneData['phone']]);
            }

            if ($socialUserId) {
                // 第三方登录在服务端授权时，此时的注册场景只需要将用户表 `users` 的 id 写入到 `social_users` 表中 user_id 做关联即可
                $this->socialUserModel->where('id', $socialUserId)->update(['user_id' => $user->id]);
            } else {
                // 直接使用 openid 注册场景时
                $input = $request->all();
                $input['openid'] = $request->openid;
                $input['user_id'] = $user->id;
                $input['social_type'] = $socialTypeCode;
                $this->socialUserModel->fill($input);
                $this->socialUserModel->save();
            }

            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw new ApiException(Code::ERR_QUERY);
        }

        $token = auth('api')->login($user);  // 会直接通过 jwt-auth 返回 token

        return $this->respondWithToken($token);
    }

    /**
     * 授权在服务端时
     *
     *  第三方授权登录（授权在服务端，需要客户端传 code 或者 access_token，「如果是微信授权登录 传 access_token 的同时，还需要传 openid 」 的情况）
     *
     * @param $request
     * @return array|bool
     * @throws ApiException
     */
    public function socialStore($request)
    {
        $socialType = strtolower($request->socialType);

        if (!in_array($socialType, SocialUser::$loginType)) {
            Code::setCode(Code::ERR_HTTP_NOT_FOUND);
            return false;
        }

        // 第三方授权登录标识 code
        $socialTypeCode = array_flip(SocialUser::$loginType)[$socialType];

        // 如果此时是 「企业微信」 授权登录
        if (SocialUser::$loginType[SocialUser::SOCIAL_QYWEIXIN] === $socialType) {
            $qyoauthUser = $this->socialAuthRepository->qywxUser($request->code);
        } else {  // socialiteproviders 包系列授权登录方式
            $oauthUser = $this->multiSocialBySocialite($request);
        }

        $socialUser = null;
        $unionid = '';
        if (SocialUser::$loginType[SocialUser::SOCIAL_WEIXIN] === $socialType) {
            // 只有在用户将公众号绑定到微信开放平台帐号后，才会出现 unionid 字段
            // 获取微信 unionid  Laravel\Socialite\AbstractUser::class@offsetExists
            $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
            if ($unionid) {
                $socialUser = SocialUser::where('unionid', $unionid)->where('social_type', $socialTypeCode)->first();
            }
        }

        if (!$socialUser) {
            // 否则直接用 openid 去查询。$oauthUser->getId() 默认为 openid
            if (SocialUser::$loginType[SocialUser::SOCIAL_QYWEIXIN] === $socialType) {
                // 如果此时为企业微信的时候
                $socialUser = SocialUser::where('openid', $qyoauthUser['openid'])->where('social_type', $socialTypeCode)->first();
                $insertData = [
                    'openid' => $qyoauthUser['openid'],
                    'name' => $qyoauthUser['name'],
                    'phone' => $qyoauthUser['phone'],
                    'sex' => $qyoauthUser['sex'],
                    'email' => $qyoauthUser['email'],
                    'headimgurl' => $qyoauthUser['headimgurl'],
                ];
            } else {
                // 如果此时为 socialiteproviders 包支持的登录方式时
                $socialUser = SocialUser::where('openid', $oauthUser->getId())->where('social_type', $socialTypeCode)->first();
                $insertData = [
                    'nickname' => $oauthUser->getNickname(),   // Laravel\Socialite\AbstractUser::class@getNickname
                    'headimgurl' => $oauthUser->getAvatar(),
                    'openid' => $oauthUser->getId(),
                    'unionid' => $unionid
                ];
            }
        }

        if (!empty($socialUser->user_id)) {
            // 如果此时已经有了 user_id ，则表示已经绑定了手机号
            $user = User::find($socialUser->user_id);
            if (empty($user)) {
                Code::setCode(Code::ERR_MODEL, '主账号不存在');
                return false;
            }
            $token = auth('api')->login($user);  // 会直接通过 jwt-auth 返回 token
            return $this->respondWithToken($token);
        }

        $insertData['social_type'] = $socialTypeCode;
        $insertData['created_at'] = date('Y-m-d H:i:s');
        $insertData['updated_at'] = date('Y-m-d H:i:s');

        // 如果此时用户已经登录，但是需要绑定第三方授权登录时
        if (auth('api')->check()) {
            $insertData['user_id'] = auth('api')->id();
        }

        if (!$socialUser) {  // 如果没有第三方授权登录的记录（ 类似于第三方授权在注册的情况下），则先将数据插入表中，之后再强制要求绑定手机号
            $id = SocialUser::insertGetId($insertData);
        }

        // 如果是用户已经登录，之后想再绑定第三方登录账号，则当数据插入之后就直接返回
        if (auth('api')->check()) return;

        $socialUserId = $id ?? $socialUser->id;

        $key = config('api.cache_key.social_user') . \Str::random(15);
        $expiredAt = now()->addMinutes(10);
        // 缓存验证码 10 分钟过期
        \Cache::put($key, ['social_user_id' => $socialUserId], $expiredAt);
        $result = [
            'social_user_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ];

        return $result;
    }

    /**
     * socialiteproviders 包系列授权登录方式
     *
     * @link https://socialiteproviders.netlify.com/providers/weixin.html
     *
     * @param $request
     * @return mixed
     * @throws ApiException
     */
    public function multiSocialBySocialite($request)
    {
        $socialType = strtolower($request->socialType);

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
                if (SocialUser::$loginType[SocialUser::SOCIAL_WEIXIN] == $socialType) {
                    $driver->setOpenId($request->openid);
                }
            }
            // 授权后获取的用户信息
            $oauthUser = $driver->userFromToken($accessToken);  // Laravel\Socialite\Two\AbstractProvider::class@userFromToken
        } catch (\Exception $e) {
            throw new ApiException(Code::ERR_PARAMS, ['参数错误，未获取用户信息']);
        }

        return $oauthUser;
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
            throw new ApiException(Code::ERR_PARAMS, ['账号或密码错误']);
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

        if ($request->new_password) {
            if (\Hash::check($request->current_password, $request->user()->password)) {
                $input['password'] = bcrypt($request->new_password);
            } else {
                throw new ApiException(Code::ERR_PARAMS, ['当前密码错误']);
            }
        }

        $data = $this->update($request->user()->id, $input);

        return $data;
    }

    /**
     * 已经登录的用户提供 openid 绑定第三方授权账号
     *
     * @param $request
     * @return bool
     */
    public function boundSocial($request)
    {
        $socialType = strtolower($request->social_type);

        if (!in_array($socialType, SocialUser::$loginType)) {
            Code::setCode(Code::ERR_HTTP_NOT_FOUND);
            return false;
        }

        // 第三方授权登录标识 code
        $socialTypeCode = array_flip(SocialUser::$loginType)[$socialType];

        $where = [
            'openid' => $request->openid,
            'social_type' => $socialTypeCode,
        ];

        $socialUser = $this->socialUserModel->where($where)->first();
        if ($socialUser) {
            Code::setCode(Code::ERR_MODEL_EXIST, '已绑定，无需重复绑定');
            return false;
        }

        $input = [
            'openid' => $request->openid,
            'unionid' => $request->unionid,
            'user_id' => auth('api')->id(),
            'social_type' => $socialTypeCode,
        ];
        $this->socialUserModel->fill($input);
        // 如果第三方授权表中没有记录，则表示需要新绑定
        $this->socialUserModel->save($input);

    }

    /**
     * 重置密码
     *
     * @param $request
     * @return bool|mixed
     * @throws \Exception
     */
    public function resetPassword($request)
    {
        $resetPwdData = cache($request->phone_key);
        if (!$resetPwdData) {
            Code::setCode(Code::ERR_PARAMS, '短信验证码已失效，请重新获取');
            return false;
        }

        if (!hash_equals($resetPwdData['phone_code'], $request->phone_code)) {
            // 输入的短信验证码错误则直接删除掉
            \Cache::forget($request->phone_key);
            Code::setCode(Code::ERR_PARAMS, '短信验证码错误');
            return false;
        }

        $user = $this->getSingleRecord($resetPwdData['phone'], 'phone');

        return $this->update($user->id, ['password' => bcrypt($request->new_password)]);
    }

    /**
     * 「暂时没用到」
     *
     * 普通方式注册（直接使用图片验证码验证）
     * 支持用户名「/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/」、中国手机号、邮箱三种账号方式
     *
     * @param $request
     * @return bool|mixed
     * @throws ApiException
     */
    public function normalRegister($request)
    {
        $captchaData = cache($request->captcha_key);
        if (!$captchaData) {
            Code::setCode(Code::ERR_PARAMS, null, ['图片验证码已失效']);
            return false;
        }

        if (!hash_equals($captchaData['captcha_code'], $request->captcha_code)) {
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

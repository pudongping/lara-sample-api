<?php
/**
 * 门户用户相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:23
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\UserRequest;
use App\Repositories\Auth\UserRepository;
use App\Models\Auth\User;

class UsersController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->init();
        $this->userRepository = $userRepository;
    }

    /**
     * 手机号注册方式，第一步，检验图片验证码有效性
     *
     * @param UserRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function checkRegister(UserRequest $request)
    {
        $data = $this->userRepository->checkRegister($request);
        return $this->response->send($data);
    }

    /**
     * 手机号注册方式，第二步，保存注册信息并直接登录
     *
     * @param UserRequest $request
     * @return mixed
     * @throws \Exception
     */
    public function register(UserRequest $request)
    {
        $data = $this->userRepository->register($request);
        return $this->response->send($data);
    }

    /**
     * 直接使用 openid 登录时，第一步，检查是否已经绑定了手机号，绑定了则直接登录
     *
     * @param $socialType
     * @param UserRequest $request
     * @return mixed
     */
    public function checkBoundPhone($socialType, UserRequest $request)
    {
        $request->merge(['socialType' => $socialType]);
        $data = $this->userRepository->checkBoundPhone($request);
        return $this->response->send($data);
    }

    /**
     * 直接使用 openid 登录时，第二步，如果此时是新账户则需要绑定手机号（需要先调用发送手机验证码的接口）
     *
     * @param $socialType
     * @param UserRequest $request
     * @return mixed
     * @throws \Exception
     */
    public function socialLogin($socialType, UserRequest $request)
    {
        $request->merge(['socialType' => $socialType]);
        $data = $this->userRepository->socialLogin($request);
        return $this->response->send($data);
    }

    /**
     * 用户名/中国手机号/邮箱登录
     *
     * @param UserRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function login(UserRequest $request)
    {
        $data = $this->userRepository->login($request);
        return $this->response->send($data);
    }

    /**
     * 第三方授权登录
     *
     * @param $socialType
     * @param UserRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function socialStore($socialType, UserRequest $request)
    {
        $request->merge(['socialType' => $socialType]);
        $data = $this->userRepository->socialStore($request);
        return $this->response->send($data);
    }

    /**
     * 某个用户的个人信息
     *
     * @param User $user
     * @param Request $request
     * @return mixed
     */
    public function show(User $user, Request $request)
    {
        return $this->response->send($user);
    }

    /**
     * 我的个人信息
     *
     * @param Request $request
     * @return mixed
     */
    public function me(Request $request)
    {
        return $this->response->send($request->user());
    }

    /**
     * 修改我的个人信息
     *
     * @param UserRequest $request
     * @return mixed
     */
    public function update(UserRequest $request)
    {
        $data = $this->userRepository->modify($request);
        return $this->response->send($data);
    }

    /**
     * 重置密码
     *
     * @param UserRequest $request
     * @return mixed
     * @throws \Exception
     */
    public function resetPwd(UserRequest $request)
    {
        $data = $this->userRepository->resetPwd($request);
        return $this->response->send($data);
    }

    /**
     * 刷新 token
     *
     * @return mixed
     */
    public function refreshToken()
    {
        $data = $this->userRepository->refreshToken();
        return $this->response->send($data);
    }

    /**
     * 退出登录
     *
     * @return mixed
     */
    public function logout()
    {
        $this->userRepository->logout();
        return $this->response->send();
    }

    /**
     * 「暂时没用到」
     *
     * 普通方式注册（直接使用图片验证码验证）
     * 支持用户名「/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/」、中国手机号、邮箱三种账号方式
     *
     * @param UserRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function normalRegister(UserRequest $request)
    {
        $data = $this->userRepository->normalRegister($request);
        return $this->response->send($data);
    }

}

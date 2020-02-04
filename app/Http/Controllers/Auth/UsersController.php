<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\UserRequest;
use App\Repositories\Auth\UserRepository;

class UsersController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->init();
        $this->userRepository = $userRepository;
    }

    /**
     * 注册
     * 支持用户名「/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/」、中国手机号、邮箱三种账号方式
     *
     * @param UserRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function register(UserRequest $request)
    {
        $data = $this->userRepository->register($request);
        return $this->response->send($data);
    }

}

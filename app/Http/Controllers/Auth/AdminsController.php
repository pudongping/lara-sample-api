<?php
/**
 * 后台管理员相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:21
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\AdminRequest;
use App\Repositories\Auth\AdminRepository;
use App\Models\Auth\Admin;

class AdminsController extends Controller
{

    protected $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->init();
        $this->adminRepository = $adminRepository;
    }

    /**
     * 用户名/中国手机号/邮箱登录
     *
     * @param AdminRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function login(AdminRequest $request)
    {
        $data = $this->adminRepository->login($request);
        return $this->response->send($data);
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
     * 用户详情信息
     *
     * @param Admin $admin
     * @return mixed
     */
    public function show(Admin $user)
    {
        return $this->response->send($user);
    }

    /**
     * 添加用户-数据处理
     *
     * @param AdminRequest $request
     * @return mixed
     * @throws \Exception
     */
    public function store(AdminRequest $request)
    {
        $user = $this->adminRepository->storage($request);
        return $this->response->send($user);
    }

    /**
     * 修改用户信息
     *
     * @param UserRequest $request
     * @return mixed
     */
    public function update(Admin $user, AdminRequest $request)
    {
        // 控制器基类使用了 「AuthorizesRequests」 trait，此 trait 提供了 authorize 方法
        // authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
        // 此处的 $admin 对应 App\Policies\Auth\AdminPolicy => updatePolicy() 中的第二个参数
        $this->authorize('updatePolicy', $user);

        $data = $this->adminRepository->modify($request);
        return $this->response->send($data);
    }

    /**
     * 刷新 token
     *
     * @return mixed
     */
    public function refreshToken()
    {
        $data = $this->adminRepository->refreshToken();
        return $this->response->send($data);
    }

    /**
     * 退出登录
     *
     * @return mixed
     */
    public function logout()
    {
        $this->adminRepository->logout();
        return $this->response->send();
    }

}

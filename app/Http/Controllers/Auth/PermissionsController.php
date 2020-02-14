<?php
/**
 * 权限相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:22
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\PermissionRequest;
use App\Repositories\Auth\PermissionsRepository;
use App\Models\Auth\Role;

class PermissionsController extends Controller
{

    protected $permissionsRepository;

    public function __construct(PermissionsRepository $permissionsRepository)
    {
        $this->init();
        $this->permissionsRepository = $permissionsRepository;
    }

    /**
     * 权限列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->permissionsRepository->getList($request);
        return $this->response->send($data);
    }

    /**
     * 创建权限显示页面
     *
     * @return mixed
     */
    public function create()
    {
        $roles = Role::select(['id', 'name', 'cn_name'])->currentGuard()->get();
        return $this->response->send($roles, ['id', 'name', 'cn_name']);
    }


    /**
     * 保存权限
     *
     * @param PermissionRequest $request
     * @return mixed
     */
    public function store(PermissionRequest $request)
    {
        $permission = $this->permissionsRepository->storage($request);
        return $this->response->send($permission);
    }


    /**
     * 编辑权限显示页面
     *
     * @param Permission $permission
     * @return mixed
     */
    public function edit(Permission $permission)
    {
        return $this->response->send($permission);
    }

    /**
     * 编辑权限数据提交
     *
     * @param PermissionRequest $request
     * @return mixed
     */
    public function update(PermissionRequest $request)
    {
        $permission = $this->permissionsRepository->modify($request);
        return $this->response->send($permission);
    }

    /**
     * 删除权限
     *
     * @param Permission $permission
     * @return mixed
     */
    public function destroy(Permission $permission)
    {
        $this->permissionsRepository->destroy($permission);
        return $this->response->send();
    }

    /**
     * 批量删除权限
     *
     * @param PermissionRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function massDestroy(PermissionRequest $request)
    {
        $this->permissionsRepository->massDestroy($request);
        return $this->response->send();
    }

}

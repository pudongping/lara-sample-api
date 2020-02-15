<?php
/**
 * 角色相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:23
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\Role;
use Illuminate\Http\Request;
use App\Repositories\Auth\RolesRepository;
use App\Models\Auth\Permission;
use App\Http\Requests\Auth\RoleRequest;

class RolesController extends Controller
{

    protected $rolesRepository;

    public function __construct(RolesRepository $rolesRepository)
    {
        $this->init();
        $this->rolesRepository = $rolesRepository;
    }

    /**
     * 角色列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->rolesRepository->getList($request);
        return $this->response->send($data);
    }

    /**
     * 创建角色显示页面
     *
     * @return mixed
     */
    public function create()
    {
        $permission = Permission::select(['id', 'name', 'cn_name'])->currentGuard()->get();
        return $this->response->send($permission, ['id', 'name', 'cn_name']);
    }

    /**
     * 创建角色数据提交
     *
     * @param RoleRequest $request
     * @return mixed
     */
    public function store(RoleRequest $request)
    {
        $roles = $this->rolesRepository->storage($request);
        return $this->response->send($roles);
    }

    /**
     * 编辑角色显示页面
     *
     * @param Role $role
     * @return mixed
     */
    public function edit(Role $role)
    {
        $permission = Permission::select(['id', 'name', 'cn_name'])->currentGuard()->get();
        $this->response->addMeta(compact('permission'));
        return $this->response->send($role);
    }

    /**
     * 编辑角色数据提交
     *
     * @param RoleRequest $request
     * @return mixed
     */
    public function update(RoleRequest $request)
    {
        $role = $this->rolesRepository->modify($request);
        return $this->response->send($role);
    }

    /**
     * 删除角色
     *
     * @param Role $role
     * @return mixed
     */
    public function destroy(Role $role)
    {
        $this->rolesRepository->destroy($role);
        return $this->response->send();
    }

    /**
     * 批量删除角色
     *
     * @param RoleRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function massDestroy(RoleRequest $request)
    {
        $this->rolesRepository->massDestroy($request);
        return $this->response->send();
    }


}

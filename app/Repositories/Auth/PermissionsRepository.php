<?php
/**
 * 权限相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:32
 */

namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Support\Code;
use App\Exceptions\ApiException;

class PermissionsRepository extends BaseRepository
{

    protected $model;

    public function __construct(Permission $permission)
    {
        $this->model = $permission;
    }

    /**
     * 权限列表
     *
     * @param $request
     * @return mixed
     */
    public function getList($request)
    {
        $search = $request->input('s');
        $model = $this->model->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->orWhere('name', 'like', '%' . $search . '%');
                $query->orWhere('cn_name', 'like', '%' . $search . '%');
            }
        });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        $model = $model->currentGuard();

        return $this->usePage($model);
    }

    /**
     * 保存权限
     *
     * @param $request
     * @return mixed
     */
    public function storage($request)
    {
        $input = $request->only('name', 'cn_name');
        $input['guard_name'] = config('api.default_guard_name');
        // 保存权限数据
        $permission = $this->store($input);

        // 如果此时选择了角色
        if (!empty($request->roles)) {
            foreach ($request->roles as $role) {
                // 循环查询当前所选角色
                $r = Role::findOrFail($role);
                // 给角色赋予权限
                $r->givePermissionTo($input['name']);
            }
        }

        return $permission;
    }

    /**
     * 更新权限
     *
     * @param $request
     * @return mixed
     */
    public function modify($request)
    {
        $input = $request->only('name', 'cn_name');
        $input['guard_name'] = config('api.default_guard_name');
        $data = $this->update($request->permission, $input);
        return $data;
    }

    /**
     * 删除权限
     *
     * @param $permission
     * @return bool
     */
    public function destroy($permission)
    {
        if (in_array($permission->name, Permission::DEFAULT_PERMISSIONS)) {
            Code::setCode(Code::ERR_PARAMS, '默认权限不允许删除');
            return false;
        }
        $permission->delete();
    }

    /**
     * 批量删除权限
     *
     * @param $request
     * @throws ApiException
     */
    public function massDestroy($request)
    {
        $permissions = $this->model->select('name')->whereIn('id', $request->ids)->get()->pluck('name')->toArray();
        foreach ($permissions as $permission) {
            if (in_array($permission, Permission::DEFAULT_PERMISSIONS)) {
                throw new ApiException(Code::ERR_PARAMS, [], '包含默认权限，不允许删除');
            }
        }
        $this->model->whereIn('id', $request->ids)->delete();
    }

    /**
     * 验证权限有效性
     *
     * @param $permissions  需要验证的权限
     * @return array  合法的权限数组
     */
    public function validatePermissions($permissions): array
    {
        $allowPermissions = [];
        if (!empty($permissions) && is_array($permissions)) {
            $allPermissions = $this->model->currentGuard()->pluck('name')->toArray();
            $allowPermissions = array_intersect($permissions, $allPermissions);
        }
        return $allowPermissions;
    }

}

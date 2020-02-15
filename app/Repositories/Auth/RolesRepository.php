<?php
/**
 * 角色相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/13
 * Time: 20:33
 */

namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;
use App\Exceptions\ApiException;
use App\Support\Code;

class RolesRepository extends BaseRepository
{

    protected $model;

    public function __construct(Role $role)
    {
        $this->model = $role;
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

        $model = $model->with('permissions')->currentGuard();

        return $this->usePage($model);
    }

    /**
     * 添加新角色数据处理
     *
     * @param $request
     * @return mixed
     */
    public function storage($request)
    {

        $input = $request->only('name', 'cn_name');
        $input['guard_name'] = config('api.default_guard_name');
        $role = $this->store($input);

        // 当前选中的所有权限 id
        $permissionsId = $request->permissions;
        if ($permissionsId) {
            $permissions = Permission::whereIn('id', $permissionsId)->get();
            // 将多个权限同步赋予到一个角色
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    /**
     *  编辑角色数据提交
     *
     * @param $request
     * @return mixed
     */
    public function modify($request)
    {
        $input = $request->only('name', 'cn_name');
        $role = $this->update($request->role, $input);

        // 先删除用户所有的权限
        \DB::table('role_has_permissions')->where('role_id', $request->role)->delete();
        // 手动重置缓存
        \Artisan::call('cache:forget spatie.permission.cache');

        // 当前选中的所有权限 id
        $permissionsId = $request->permissions;
        if ($permissionsId) {
            $permissions = Permission::whereIn('id', $permissionsId)->get();
            // 将多个权限同步赋予到一个角色
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    /**
     * 删除角色
     *
     * @param $role
     * @return array
     */
    public function destroy($role)
    {
        if (in_array($role->name, Role::DEFAULT_ROLES)) {
            Code::setCode(Code::ERR_PARAMS, '默认角色不允许删除');
            return false;
        }
        $role->delete();
    }

    /**
     * 批量删除角色
     *
     * @param $request
     * @throws ApiException
     */
    public function massDestroy($request)
    {
        $roles = $this->model->select('name')->whereIn('id', $request->ids)->get()->pluck('name')->toArray();
        foreach ($roles as $role) {
            if (in_array($role, Role::DEFAULT_ROLES)) {
                throw new ApiException(Code::ERR_PARAMS, [], '包含默认角色，不允许删除');
            }
        }
        $this->model->whereIn('id', $request->ids)->delete();
    }

}

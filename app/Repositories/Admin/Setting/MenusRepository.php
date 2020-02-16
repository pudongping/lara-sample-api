<?php
/**
 * 菜单相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/15
 * Time: 20:42
 */

namespace App\Repositories\Admin\Setting;

use App\Repositories\BaseRepository;
use App\Models\Admin\Setting\Menu;
use App\Repositories\Auth\PermissionsRepository;

class MenusRepository extends BaseRepository
{

    protected $model;
    protected $permissionsRepository;

    public function __construct(
        Menu $menu,
        PermissionsRepository $permissionsRepository
    ) {
        $this->model = $menu;
        $this->permissionsRepository = $permissionsRepository;
    }

    /**
     * 路由菜单列表
     *
     * @param $request
     * @return mixed
     */
    public function getList($request)
    {
        $search = $request->input('s');
        $model = $this->model->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->orWhere('route_name', 'like', '%' . $search . '%');
                $query->orWhere('cn_name', 'like', '%' . $search . '%');
                $query->orWhere('permission', 'like', '%' . $search . '%');
            }
        });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        if (!is_null($request->state)) {
            $model = $model->where('state', intval(boolval($request->state)));
        }

        if (!is_null($request->type)) {
            $model = $model->where('type', intval(boolval($request->type)));
        }

        return $this->usePage($model);
    }

    /**
     * 添加菜单-数据处理
     *
     * @param $request
     * @return mixed
     */
    public function storage($request)
    {
        $input = $this->fetchParams($request);
        return $this->store($input);
    }

    /**
     * 编辑菜单-数据处理
     *
     * @param $request
     * @return mixed
     */
    public function modify($request)
    {
        $input = $this->fetchParams($request);
        return $this->update($request->menu->id, $input);
    }

    /**
     * 获取参数
     *
     * @param $request
     * @return mixed
     */
    public function fetchParams($request)
    {
        $input = $request->only(['pid', 'route_name', 'cn_name', 'icon', 'extra', 'description', 'sort', 'state', 'type']);
        $allowPermissions = $this->permissionsRepository->validatePermissions($request->permission);
        $input['permission'] = join('|', $allowPermissions);
        return $input;
    }



}

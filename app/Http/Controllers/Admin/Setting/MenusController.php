<?php
/**
 * 菜单相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/15
 * Time: 20:42
 */

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Admin\Setting\MenusRepository;
use App\Http\Requests\Admin\Setting\MenuRequest;
use App\Models\Admin\Setting\Menu;

class MenusController extends Controller
{

    protected $menusRepository;

    public function __construct(MenusRepository $menusRepository)
    {
        $this->init();
        $this->menusRepository = $menusRepository;
    }

    /**
     * 路由菜单列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->menusRepository->getList($request);
        return $this->response->send($data);
    }

    /**
     * 添加菜单-数据处理
     *
     * @param MenuRequest $request
     * @return mixed
     */
    public function store(MenuRequest $request)
    {
        $data = $this->menusRepository->storage($request);
        return $this->response->send($data);
    }

    /**
     * 编辑菜单-显示页面
     *
     * @param Menu $menu
     * @return mixed
     */
    public function edit(Menu $menu)
    {
        return $this->response->send($menu);
    }

    /**
     * 编辑菜单-数据处理
     *
     * @param MenuRequest $request
     * @param Menu $menu
     * @return mixed
     */
    public function update(MenuRequest $request, Menu $menu)
    {
        $data = $this->menusRepository->modify($request);
        return $this->response->send($data);
    }

    /**
     * 删除菜单
     *
     * @param Menu $menu
     * @return mixed
     * @throws \Exception
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return $this->response->send();
    }
}

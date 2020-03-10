<?php
/**
 * 商品类目
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/2
 * Time: 9:38
 */

namespace App\Repositories\Admin\Product;

use App\Repositories\BaseRepository;
use App\Models\Product\ProductCategory;
use App\Support\Code;

class ProductCategoryRepository extends BaseRepository
{

    protected $model;

    public function __construct(ProductCategory $productCategoryModel)
    {
        $this->model = $productCategoryModel;
    }

    public function getList($request)
    {
        $search = $request->input('s');
        $model = $this->model->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->orWhere('name', 'like', '%' . $search . '%');
                $query->orWhere('description', 'like', '%' . $search . '%');
            }
        });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        if (!is_null($request->status)) {
            $model = $model->where('status', intval(boolval($request->status)));
        }

        return $this->usePage($model);
    }

    /**
     * 类目树型结构
     *
     * @return array
     */
    public function categoryTree()
    {
        $result = ProductCategory::all('id', 'pid', 'name')->toArray();
        $result = make_tree_data($result);
        return $result;
    }

    /**
     * 所有可见的类目树型结构
     *
     * @return array
     */
    public function allCateTree()
    {
        $allCate = $this->model->select('id', 'pid', 'name')->allowStatus()->get()->toArray();
        return make_tree_data($allCate);
    }

    /**
     * 添加类目
     *
     * @param $request
     * @return mixed
     */
    public function storage($request)
    {
        $input = $request->only(['pid', 'name', 'description', 'sort', 'status']);
        return $this->store($input);
    }

    /**
     * 编辑类目-数据处理
     *
     * @param $request
     * @return bool|mixed
     */
    public function modify($request)
    {
        $input = $request->only(['pid', 'name', 'description', 'sort', 'status']);
        if ($request->category->id == $request->pid) {
            Code::setCode(Code::ERR_PARAMS, '不可以将自身添加成父级类目');
            return false;
        }
        return $this->update($request->category->id, $input);
    }

    /**
     * 检查类目 id 的有效性
     *
     * @param array $cateIds
     * @return array
     */
    public function checkCateIds(array $cateIds) : array
    {
       return $this->model->whereIn('id', $cateIds)->pluck('id')->toArray();
    }


}

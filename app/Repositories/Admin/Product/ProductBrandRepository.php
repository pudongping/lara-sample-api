<?php
/**
 * 商品品牌相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/2
 * Time: 22:38
 */

namespace App\Repositories\Admin\Product;

use App\Repositories\BaseRepository;
use App\Models\Product\ProductBrand;
use App\Exceptions\ApiException;
use App\Support\Code;
use App\Repositories\Admin\Product\ProductCategoryRepository;

class ProductBrandRepository extends BaseRepository
{

    protected $model;
    protected $productCategoryRepository;

    public function __construct(
        ProductBrand $productBrandModel,
        ProductCategoryRepository $productCategoryRepository
    ) {
        $this->model = $productBrandModel;
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * 品牌列表
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
                 $query->orWhere('description', 'like', '%' . $search . '%');
             }
         });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        if (!is_null($request->status)) {
            $model = $model->where('status', intval(boolval($request->status)));
        }

        $model = $model->with('categories');

        return $this->usePage($model);
    }

    /**
     * 所有可见的品牌
     *
     * @return mixed
     */
    public function allBrands()
    {
        return $this->model->select(['id', 'name', 'description', 'log_url'])->allowStatus()->get()->toArray();
    }

    /**
     * 新建品牌
     *
     * @param $request
     * @return mixed
     * @throws ApiException
     */
    public function storage($request)
    {
        $input = $request->only('name', 'description', 'status', 'sort');

        $validateCateIds = $this->productCategoryRepository->checkCateIds($request->category_ids);
        if (empty($validateCateIds)) {
            Code::setCode(Code::ERR_PARAMS, '类目参数不合法');
            return false;
        }

        \DB::beginTransaction();
        try {
            $brand = $this->store($input);
            // 多对多插入关联表
            $brand->categories()->attach($validateCateIds);
            \DB::commit();
        } catch (\Exception $exception) {
            throw new ApiException(Code::ERR_QUERY);
            \DB::rollBack();
        }

        return $brand;
    }

    /**
     *  品牌编辑-数据提交
     *
     * @param $request
     * @return bool|mixed
     * @throws ApiException
     */
    public function modify($request)
    {
        $input = $request->only('name', 'description', 'status', 'sort');

        $validateCateIds = $this->productCategoryRepository->checkCateIds($request->category_ids);
        if (empty($validateCateIds)) {
            Code::setCode(Code::ERR_PARAMS, '类目参数不合法');
            return false;
        }

        \DB::beginTransaction();
        try {
            $brand = $this->update($request->brand->id, $input);
            // 多对多插入关联表（先删除关联数据，后写入）
            $brand->categories()->sync($validateCateIds);
            \DB::commit();
        } catch (\Exception $exception) {
            throw new ApiException(Code::ERR_QUERY);
            \DB::rollBack();
        }

        return $brand;
    }


}

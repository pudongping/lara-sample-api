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
            $insertData = $this->makeCateBrandIds($validateCateIds, $brand->id);
            \DB::table('product_categories_pivot_brands')->insert($insertData);
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

            // 先删除关联表中的数据
            \DB::table('product_categories_pivot_brands')->where('brand_id', $request->brand->id)->delete();
            $insertData = $this->makeCateBrandIds($validateCateIds, $request->brand->id);
            \DB::table('product_categories_pivot_brands')->insert($insertData);
            \DB::commit();

        } catch (\Exception $exception) {
            throw new ApiException(Code::ERR_QUERY);
            \DB::rollBack();
        }
        return $brand;
    }

    /**
     * 拼接 品牌-类目 关联表所需数据
     *
     * @param array $categoryIds
     * @param $brandId
     * @return array
     */
    private function makeCateBrandIds(array $categoryIds, $brandId) : array
    {
        $data = [];
        $item = [];
        foreach ($categoryIds as $categoryId) {
            $item['category_id'] = intval($categoryId);
            $item['brand_id'] = $brandId;
            $item['created_at'] = now();
            $item['updated_at'] = now();
            $data[] = $item;
        }
        return $data;
    }

}

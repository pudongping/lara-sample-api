<?php
/**
 * 门户页面-商品相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/10
 * Time: 12:08
 */

namespace App\Repositories\Api\Product;

use App\Repositories\BaseRepository;
use App\Models\Product\ProductSpu;

class ProductSpuRepository extends BaseRepository
{

    protected $model;
    protected $productCategoryModel;

    public function __construct(
        ProductSpu $productSpuMoel
    ) {
        $this->model = $productSpuMoel;
    }

    /**
     * 商品列表
     *
     * @param $request
     * @return mixed
     */
    public function getList($request)
    {
        $search = $request->input('s');
        $categoryId = $request->category_id;
        $brandId = $request->brand_id;

        $model = $this->model->where(function ($query) use ($search, $categoryId) {
            if (!empty($search)) {
                $query->orWhere('title', 'like', '%' . $search . '%');
                $query->orWhere('keywords', 'like', '%' . $search . '%');
            }
            if (!empty($categoryId)) {
                $query->orWhereHas('categories', function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId);
                });
            }
        });

        if (!is_null($brandId)) {
            $model = $model->orWhere('brand_id', intval($brandId));
        }

        $fields = [
            'id', 'title', 'unit', 'sketch', 'keywords', 'tags', 'price', 'market_price', 'rating',
            'virtual_retail_num', 'warning_stock', 'main_image', 'slider_image', 'description',
        ];

        $model = $model->with('categories', 'brand')->select($fields)->allowStatus();

        return $this->usePage($model);
    }


}

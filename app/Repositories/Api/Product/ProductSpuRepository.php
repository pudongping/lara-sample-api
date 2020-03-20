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
use App\Models\Product\ProductSku;
use App\Models\Product\ProductAttribute;
use Illuminate\Support\Collection;

class ProductSpuRepository extends BaseRepository
{

    protected $model;
    protected $productSkuModel;
    protected $productAttributeModel;

    public function __construct(
        ProductSpu $productSpuMoel,
        ProductSku $productSkuModel,
        ProductAttribute $productAttributeModel
    ) {
        $this->model = $productSpuMoel;
        $this->productSkuModel = $productSkuModel;
        $this->productAttributeModel = $productAttributeModel;
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
            'virtual_retail_num', 'warning_stock', 'main_image', 'slider_image', 'description'
        ];

        $model = $model->with('categories', 'brand')->select($fields)->allowStatus();

        return $this->usePage($model);
    }

    /**
     * 商品详情
     *
     * @param $request
     * @return mixed
     */
    public function detail($request)
    {
        $fields = [
            'id', 'brand_id', 'title', 'unit', 'sketch', 'keywords', 'tags', 'price', 'market_price', 'rating',
            'sold_count', 'review_count', 'virtual_retail_num', 'description', 'stock', 'main_image', 'slider_image'
        ];
        $spu = $this->model->with([
            'brand' => function ($query) {
                $query->select('id', 'name', 'description', 'log_url')->allowStatus();
            },
            'categories' => function ($query) {
                $query->select('id', 'name', 'description')->allowStatus();
            }
        ])->select($fields)->allowStatus()->findOrFail($request->id);

        $skus = $this->productSkuModel
            ->select('id', 'spu_id', 'name', 'description', 'main_url', 'price', 'stock', 'key_attr_option')
            ->where('spu_id', $request->id)
            ->get();

        // 寻找需要前端禁用掉的属性选项
        $unableAttrOpt = self::findNeedUnableAttrOpt($skus);

        // 属性-属性值关联数组
        $attrs = $this->productAttributeModel->with(['attributeOptions' => function ($query) {
            $query->select('id', 'attribute_id', 'name')->dataSort();
        }])->select('id', 'spu_id', 'name')
            ->where('spu_id', $request->id)
            ->dataSort()
            ->get()
            ->toArray();

        // 添加是否禁用销售属性选项到属性-属性选项关联数组中
        $attrsDisable = self::addIsDisableAttr($attrs, $unableAttrOpt);

        $spuArr = $spu->toArray();
        $spuArr['attrs'] = $attrsDisable;
        $spuArr['skus'] = $skus;

        return $spuArr;
    }

    /**
     * 寻找需要前端禁用掉的属性选项（商品属性选项售罄禁止点击按钮）
     *
     * @param Collection $skus  当前商品的 sku 集合
     * @return array  需要禁用掉的 「属性-属性值拼接而成的字符串」数组
     */
    public static function findNeedUnableAttrOpt(Collection $skus) : array
    {
        if ($skus->isEmpty()) return [];

        // 过滤出所有库存为 0 的 sku 数据
        $noStockSkus = $skus->where('stock', 0)->pluck('key_attr_option')->toArray();
        // 将所有没有库存的 sku 的属性属性值字符串转换成数组
        $noStockAttrOpts = array_map(function ($value) {
            return array_merge([], str_explode($value, '|'));
        }, $noStockSkus);
        // 没有库存的 sku 「属性和属性值拼接字符串」的所有可能性
        $noStockAttrOptsArr = array_reduce($noStockAttrOpts, 'array_merge', []);

        // 有库存的 sku 的属性和属性值数组
        $hasStockSkus = $skus->where('stock', '>', 0)->pluck('key_attr_option')->toArray();
        $hasStockAttrOpts = array_map(function ($value) {
            return array_merge([], str_explode($value, '|'));
        }, $hasStockSkus);
        // 有库存的 sku 「属性和属性值拼接字符串」的所有可能性
        $hasStockAttrOptsArr = array_reduce($hasStockAttrOpts, 'array_merge', []);

        // 商品属性选项售罄禁止点击按钮逻辑为：当前属性选项在 sku 中库存均为 0 时，才禁止点击
        $needUnableAttrOpt = array_map(function ($v) use ($hasStockAttrOptsArr) {
            $data = [];
            if (!in_array($v, $hasStockAttrOptsArr)) {
                $data[] = $v;
            }
            return $data;
        }, $noStockAttrOptsArr);

        // 从里到外：删除空值、二维数组转一维数组、去重
        return array_unique(array_reduce(array_filter($needUnableAttrOpt), 'array_merge', []));
    }

    /**
     * 添加是否禁用销售属性选项到属性-属性选项关联数组中
     *
     * @param array $attrs  属性及属性选项关联数组
     * @param array $unableAttrOpt  需要禁用掉的 「属性-属性值拼接而成的字符串」数组
     * @return array  组装好的属性及属性选项关联数组
     */
    public static function addIsDisableAttr(array $attrs, array $unableAttrOpt) : array
    {
        foreach ($attrs as $k => $v) {
            foreach ($v['attribute_options'] as $kk => $vv) {
                $attrOptStr = $vv['attribute_id'] . '_' . $vv['id'];
                if (in_array($attrOptStr, $unableAttrOpt)) {
                    $attrs[$k]['attribute_options'][$kk]['is_disable'] = true;
                } else {
                    $attrs[$k]['attribute_options'][$kk]['is_disable'] = false;
                }
                $attrs[$k]['attribute_options'][$kk]['attr_opt_str'] = $attrOptStr;
            }
        }
        return $attrs;
    }


}

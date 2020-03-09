<?php
/**
 * 商品相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/4
 * Time: 23:39
 */

namespace App\Repositories\Admin\Product;

use App\Models\Product\ProductAttributeOption;
use App\Repositories\BaseRepository;
use App\Models\Product\ProductSpu;
use App\Models\Product\ProductSku;
use App\Repositories\Admin\Product\ProductCategoryRepository;
use App\Support\Code;
use App\Exceptions\ApiException;
use App\Handlers\CarteSianHandler;
use App\Models\Product\ProductAttribute;
use App\Models\Product\ProductAttributeSkuOption;
use App\Handlers\FactorialHandler;

class ProductSpuRepository extends BaseRepository
{

    protected $model;
    protected $productSkuModel;
    protected $productAttributeModel;
    protected $productAttributeOptionModel;
    protected $productCategoryRepository;
    protected $factorialHandler;

    public function __construct(
        ProductSpu $productSpuModel,
        productSku $productSkuModel,
        ProductCategoryRepository $productCategoryRepository,
        ProductAttribute $productAttribute,
        ProductAttributeOption $productAttributeOption,
        FactorialHandler $factorialHandler
    ) {
        $this->model = $productSpuModel;
        $this->productSkuModel = $productSkuModel;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productAttributeModel = $productAttribute;
        $this->productAttributeOptionModel = $productAttributeOption;
        $this->factorialHandler = $factorialHandler;
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

        $model = $this->model->where(function ($query) use ($search, $categoryId) {
            if (!empty($search)) {
                $query->orWhere('title', 'like', '%' . $search . '%');
                $query->orWhere('keywords', 'like', '%' . $search . '%');
                $query->orWhere('barcode', 'like', '%' . $search . '%');
            }
            if (!empty($categoryId)) {
                $query->orWhereHas('categories', function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId);
                });
            }
        });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        if (!is_null($request->brand_id)) {
            $model = $model->where('brand_id', intval($request->brand_id));
        }

        $model = $model->with('categories', 'brand');

        return $this->usePage($model);
    }

    /**
     * 添加主商品
     *
     * @param $request
     * @return bool|mixed
     * @throws ApiException
     */
    public function storage($request)
    {
        $input = $request->all();

        $validateCateIds = $this->productCategoryRepository->checkCateIds($request->category_ids);
        if (empty($validateCateIds)) {
            Code::setCode(Code::ERR_PARAMS, '类目参数不合法');
            return false;
        }

        if (! count($request->slider_image)) {
            Code::setCode(Code::ERR_PARAMS, '至少上传一张轮播图');
            return false;
        }

        // 用合法的分类 id 替换掉传参的值
        $input['category_ids'] = $validateCateIds;

        \DB::beginTransaction();
        try {
            $spu = $this->store($input);
            // 多对多插入关联表
            $spu->categories()->attach($validateCateIds);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw new ApiException(Code::ERR_QUERY);
        }

        return $spu;
    }

    /**
     * 编辑主商品-数据提交
     *
     * @param $request
     * @return bool|mixed
     * @throws ApiException
     */
    public function modify($request)
    {
        $input = $request->all();

        $validateCateIds = $this->productCategoryRepository->checkCateIds($request->category_ids);
        if (empty($validateCateIds)) {
            Code::setCode(Code::ERR_PARAMS, '类目参数不合法');
            return false;
        }

        if (! count($request->slider_image)) {
            Code::setCode(Code::ERR_PARAMS, '至少上传一张轮播图');
            return false;
        }

        // 用合法的分类 id 替换掉传参的值
        $input['category_ids'] = $validateCateIds;

        \DB::beginTransaction();
        try {
            $spu = $this->update($request->spu->id, $input);
            // 多对多插入关联表（先删除关联数据，后写入）
            $spu->categories()->sync($validateCateIds);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw new ApiException(Code::ERR_QUERY);
        }

        return $spu;
    }

    /**
     * 获取 sku 模板数据
     *
     * @param $request
     * @return array
     */
    public function getSkusTemplate($request)
    {
        $attrs = $request->spu->attrs()->dataSort()->pluck('name', 'id')->toArray();  // 商品所有的属性
        // 商品所有的属性选项值
        $attrOptions = $this->productAttributeOptionModel
            ->select('id', 'attribute_id', 'name')
            ->whereIn('attribute_id', array_keys($attrs))
            ->dataSort()
            ->get()
            ->groupBy('attribute_id')
            ->toArray();

        // 得到销售属性和销售属性选项值之间的关联关系
        $attrOptRel = $this->getAttrOptionsRelation($attrs, $attrOptions);

        // 「属性-属性值」与「属性-属性值」的笛卡尔乘积
        $carteSianIns = new CarteSianHandler();
        $attrOptionCarteSian = $carteSianIns->getCarteSianData($attrOptRel['attrOptionKey']);
        // 拼接笛卡尔乘积到 sku 模板数据中
        $preSkusTemp = $this->preSkusTemplateData($attrOptionCarteSian);

        // 用 sku 表中的数据替换掉模板中的数据
        $options = $this->attemptFetchSkusData($request, $preSkusTemp);

        return ['attrs' => $attrOptRel['attrs'], 'options' => $options];
    }

    /**
     * 得到销售属性和销售属性选项值之间的关联关系
     *
     * @param array $attrs  所有的销售属性数组
     * @param array $attrOptions  所有的销售属性选项数组
     * @return array
     */
    public function getAttrOptionsRelation(array $attrs, array $attrOptions) : array
    {
        $dataAttrs = [];  // 用于记录所有的属性值和属性选项值数组
        $itemData = [];
        $attrOptionKey = [];  // 用于记录属性及 id 和属性选项值及 id 的关联关系
        foreach ($attrs as $key => $attr) {
            $itemData['attr'] = $attr;  // 单个属性值
            if (isset($attrOptions[$key])) {  // 避免出现有属性选项没有属性值的情况
                $optionItem = [];
                foreach ($attrOptions[$key] as $k => $option) {
                    $optionItem[] = $option['name'];  // 单个属性选项值
                    $attrOptionKey[$key][$k] = $attr . '_' . $key . '|' . $option['name'] . '_' . $option['id'];
                }
                $itemData['options'] = $optionItem;
            }
            $dataAttrs[] = $itemData;
        }

        // $dataAttrs = [['attr' => '颜色', 'options' => ['黑色', '白色', '咖啡色']]];
        // $attrOptionKey = [['颜色_1|黑色_1', '颜色_1|白色_2', '颜色_1|咖啡色_3']];

        return ['attrs' => $dataAttrs, 'attrOptionKey' => $attrOptionKey];
    }

    /**
     * 拼接 sku 模板所需数据
     *
     * @param array $cartSianData  属性和属性值产生的笛卡尔乘积
     * @return array
     */
    private function preSkusTemplateData(array $cartSianData) : array
    {
//        $cartSianData = [
//            ['颜色_1|黑色_1', '尺寸_2|S_4', '材质_3|羽绒_7'],
//            ['颜色_1|黑色_1', '尺寸_2|S_4', '材质_3|鹅绒_8'],
//            ['颜色_1|黑色_1', '尺寸_2|M_5', '材质_3|羽绒_7'],
//            ['颜色_1|黑色_1', '尺寸_2|M_5', '材质_3|鹅绒_8'],
//            ['颜色_1|黑色_1', '尺寸_2|L_6', '材质_3|羽绒_7'],
//            ['颜色_1|黑色_1', '尺寸_2|L_6', '材质_3|鹅绒_8'],
//        ];
        $data = [];
        $item = [];
        foreach ($cartSianData as $k => $v) {
            $arrtOptionIdStr = '';  // 记录属性 id 和属性选项的 id 对应关系
            foreach ($v as $kk => $vv) {
                list($attrId, $optionId) = explode('|', $vv);  // 切割属性值和属性选项的分隔
                list($attr, $idAttr) = explode('_', $attrId);
                list($option, $idOption) = explode('_', $optionId);
                $item[$attr] = $option;  // 属性值为 key，属性选项为 value
                $arrtOptionIdStr .= $idAttr . '_' . $idOption . '|';
            }
            $data[$k]['attr'] = $item;
            $data[$k]['ids_str'] = $arrtOptionIdStr;
            $data[$k]['price'] = '0.00';  // 价格采用 decimal(10,2) 类型保存
            $data[$k]['stock'] = 0;
            $data[$k]['main_url'] = '';
            $data[$k]['sku_id'] = 0;  // sku 表的id，如果是新增时模板数据为 0，如果是更新时，则为当前 sku 的 id
        }

//        $data = [
//            ['attr' => ['颜色' => '黑色', '尺寸' => 'S', '材质' => '羽绒'], 'ids_str' => '1_1|2_4|3_7|', 'price' => '0.00', 'stock' => 0, 'main_url' => '', 'sku_id' => 0],
//            ['attr' => ['颜色' => '黑色', '尺寸' => 'S', '材质' => '鹅绒'], 'ids_str' => '1_1|2_4|3_8|', 'price' => '0.00', 'stock' => 0, 'main_url' => '', 'sku_id' => 0],
//            ['attr' => ['颜色' => '黑色', '尺寸' => 'M', '材质' => '羽绒'], 'ids_str' => '1_1|2_5|3_7|', 'price' => '0.00', 'stock' => 0, 'main_url' => '', 'sku_id' => 0],
//            ['attr' => ['颜色' => '黑色', '尺寸' => 'M', '材质' => '鹅绒'], 'ids_str' => '1_1|2_5|3_8|', 'price' => '0.00', 'stock' => 0, 'main_url' => '', 'sku_id' => 0],
//            ['attr' => ['颜色' => '黑色', '尺寸' => 'L', '材质' => '羽绒'], 'ids_str' => '1_1|2_6|3_7|', 'price' => '0.00', 'stock' => 0, 'main_url' => '', 'sku_id' => 0],
//            ['attr' => ['颜色' => '黑色', '尺寸' => 'L', '材质' => '鹅绒'], 'ids_str' => '1_1|2_6|3_8|', 'price' => '0.00', 'stock' => 0, 'main_url' => '', 'sku_id' => 0],
//        ];

        return $data;
    }

    /**
     * 获取当前商品的 sku 数据，从而替换掉 sku 模板数据中的指定值
     *
     * @param $request
     * @param array $preSkusTemp
     * @return array
     */
    public function attemptFetchSkusData($request, array $preSkusTemp) : array
    {
        $skuArr = $request->spu->skus->toArray();  // 当前商品的 sku

        foreach ($preSkusTemp as $k => &$v) {
            foreach ($skuArr as $kk => $vv) {
                // 获取当前商品 sku 所有的属性和属性值的排列组合（因为 2_3|1_2| 和 1_2|2_3| 属于同一个 sku）
                $currentKeyAllCombine = $this->getKeyInAllCombineArr($vv['key_attr_option']);
                if (in_array($v['ids_str'], $currentKeyAllCombine)) {
                    // 这里完全可以使用 key_attr_option 字段的值来找关联关系，
                    // 因为 key_attr_option 字段的值一定是从 「属性-属性选项」 笛卡尔乘积中产生
                    // 如果当前的 ids_str 在当前 sku 所有的属性和属性值排列组合中，则需要将相应的「sku 价格、库存等」覆盖掉数据模板中的值
                    $v['price'] = $vv['price'];
                    $v['stock'] = $vv['stock'];
                    $v['main_url'] = $vv['main_url'];
                    $v['sku_id'] = $vv['id'];
                }
            }
        }

        return $preSkusTemp;
    }

    /**
     * 获取当前商品 sku 所有的属性和属性值的排列组合（因为 2_3|1_2| 和 1_2|2_3| 属于同一个 sku）
     *
     * @param string $keyAttrOptStr  「商品属性-商品属性选项」字符串，eg：2_3|1_2
     * @return array
     */
    private function getKeyInAllCombineArr(string $keyAttrOptStr) : array
    {
        $strArr = str_explode($keyAttrOptStr, '|');
        $allCombineArr = $this->factorialHandler->getArrAllCombineByFactor($strArr);
        foreach ($allCombineArr as &$value) {
            $value = $value . '|';
        }
        return $allCombineArr;
    }

    /**
     * 添加 「属性-属性选项值」 或者 更新 「属性-属性选项值」
     *
     * 以提交的数据为主，如果数据库中有，提交的数据没有，则删除数据库中的数据
     * 如果数据库中没有，提交的数据有，则新建新数据并且删除数据库中和提交的不一致的数据
     *
     * @param $request
     * @return bool
     * @throws ApiException
     */
    public function attrOptStoreOrUpdate($request)
    {
        // {"attrs":[{"attr":"颜色","options":["黑色","白色","咖啡色"]},{"attr":"尺寸","options":["S","M","L"]},{"attr":"材质","options":["羽绒","鹅绒"]}]}
        $attrs = $request->attrs;
        $spuId = $request->spu->id;

        if (
            empty($attrs)
            || (!isset($attrs[0]['attr']) || empty($attrs[0]['attr']))
            || (!isset($attrs[0]['options'][0]) || empty($attrs[0]['options'][0]))
        ) {
            Code::setCode(Code::ERR_PARAMS, '属性值不能为空，且必须至少含有一个属性值、一个属性选项');
            return false;
        }

        // 当前商品所有的属性
        $attrIds = $request->spu->attrs->pluck('id', 'name')->toArray();
        // 当前商品所有的属性选项值
        $attrOpts = $this->productAttributeOptionModel
            ->select('id', 'attribute_id', 'name')
            ->whereIn('attribute_id', array_values($attrIds))
            ->get()
            ->groupBy('attribute_id')
            ->toArray();

        // 属性 id - 属性选项 （二维数组）
        foreach ($attrOpts as $attrKey => &$attrVal) {
            $attrVal = collect($attrVal)->pluck('id', 'name')->toArray();
        }

        \DB::beginTransaction();
        try {

            foreach ($attrs as $k => $v) {
                $attrInput['spu_id'] = $spuId;
                if (isset($v['attr']) && !empty(trim($v['attr']))) {  // 只有属性值不为空的属性名称，才会进行下一步操作

                    // 如果当前提交的属性在数据库中存在，则需要进一步判断属性选项是否在数据库中存在
                    if (isset($attrIds[$v['attr']])) {
                        $currentAttrId = $attrIds[$v['attr']];  // 当前属性 id

                        // 如果提交的属性需要排序，则还需要更新排序参数
                        if (isset($v['attr_sort'])) {
                            $this->productAttributeModel->where('id', $currentAttrId)->update(['sort' => intval($v['attr_sort'])]);
                        }

                        if (isset($v['options'])) {
                            foreach ($v['options'] as $kkk => $vvv) {
                                // $attrOpts[$currentAttrId] 为 当前属性选项数组 （key => 属性选项名称, value => 属性选项 id）
                                if (isset($attrOpts[$currentAttrId][$vvv])) {
                                    $currentAttrOptId = $attrOpts[$currentAttrId][$vvv];  // 当前属性选项的 id
                                    if (isset($v['opt_sort'][$kkk])) {  // 更新属性值排序
                                        $this->productAttributeOptionModel->where('id', $currentAttrOptId)->update(['sort' => intval($v['opt_sort'][$kkk])]);
                                    }
                                    // 如果当前提交的属性选项已经在数据库中存在，则移除掉「当前提交的且在数据库中同时存在的」同名属性选项
                                    // 剩下的就是只在数据库中存在，但是非提交的属性选项值（需要删除）
                                    unset($attrOpts[$currentAttrId][$vvv]);
                                } else {
                                    // 提交的属性选项数据库中没有，则为新增的数据库选项
                                    if (! empty(trim($vvv))) {
                                        $optInput = [];
                                        if (isset($v['opt_sort'][$kkk])) {
                                            $optInput['sort'] = (int)$v['opt_sort'][$kkk];  // 属性选项排序
                                        }
                                        $optInput['attribute_id'] = $currentAttrId;
                                        $optInput['name'] = $vvv;  // 提交的属性选项值
                                        ProductAttributeOption::create($optInput);
                                    }
                                }
                            }
                        }

                        unset($attrIds[$v['attr']]);  // 删除掉当前提交的且在数据库中存在的同名属性，剩下的就是只有数据库中存在的属性（需要删除）
                    } else {  // 如果当前提交的属性在数据库中不存在，则直接新建属性

                        $attrInput = [];
                        $attrInput['name'] = $v['attr'];  // 属性名称
                        if (isset($v['attr_sort']) && !empty($v['attr_sort'])) {
                            $attrInput['sort'] = (int)$v['attr_sort'];  // 属性名称排序
                        }
                        $attrRes = $request->spu->attrs()->create($attrInput);  // 将属性写入属性表
                        if (isset($v['options'])) {
                            $optionInput = [];
                            foreach ($v['options'] as $kk => $vv) {
                                if (isset($v['opt_sort'][$kk])) {
                                    $optionInput['sort'] = (int)$v['opt_sort'][$kk];  // 属性选项排序
                                }
                                $optionInput['attribute_id'] = $attrRes->id;  // 当前的属性 id
                                if (! empty(trim($vv))) {  // 只有属性选项值不为空的属性选项，才会插入数据库
                                    $optionInput['name'] = $vv;  // 当前的属性选项
                                    $attrRes->attributeOptions()->create($optionInput);  // 将属性选项值写入属性选项值表
                                }
                            }
                        }

                    }

                }
            }

            if (!empty($attrIds)) {
                // 如果此时存在「数据库中含有的属性但是在提交属性中不存在的属性」，那么就需要删除数据库中的属性（以提交的数据为主）
                // 删除属性值表的关联数据
                $this->productAttributeModel->whereIn('id', array_values($attrIds))->delete();
                // 删除掉 「sku-属性-属性选项关联表」中的数据
                ProductAttributeSkuOption::whereIn('attribute_id', array_values($attrIds))->delete();
            }

            if (!empty($attrOpts)) {
                $attrOptValId = array_reduce($attrOpts,"array_merge", []);
                $optionIds = array_values($attrOptValId);  // 需要删除的属性选项值 id 数组
                // 删除属性选项值表的关联数据
                $this->productAttributeOptionModel->whereIn('id', $optionIds)->delete();
                // 删除掉 「sku-属性-属性选项关联表」中的数据
                ProductAttributeSkuOption::whereIn('option_id', $optionIds)->delete();
            }

            \DB::commit();
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            \DB::rollBack();
            throw new ApiException(Code::ERR_QUERY);
        }
    }

    /**
     * 新建 sku 或者 更新 sku 数据
     *
     * @param $request
     * @throws ApiException
     */
    public function skuStoreOrUpdate($request)
    {
        // 准备 sku 表和 arrtibute_sku_options 表中所需要插入的数据，并检查数据的有效性
        $data = $this->preAttrSkuOptData($request);

        $skuIds = $request->spu->skus->pluck('id', 'id')->toArray();  // 当前商品所有的 sku id 数组

        \DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if (isset($skuIds[$item['sku_id']])) {  // 如果当前提交的 sku 数据中的 sku_id 在 sku 数据库中，则需要更新操作
                    $skuUpdate['key_attr_option'] = $item['key_attr_option'];
                    $skuUpdate['price'] = $item['price'];
                    $skuUpdate['stock'] = $item['stock'];
                    $skuUpdate['main_url'] = $item['main_url'];
                    $this->productSkuModel->where('id', $item['sku_id'])->update($skuUpdate);
                    // 移除掉提交参数和 sku 数据库中的并集 sku_id，则剩下的 sku_id 即为数据库中独有，提交参数不具有的。（就需要删除）
                    unset($skuIds[$item['sku_id']]);
                } else {  // 否则，就是新建 sku 操作
                    // 将数据插入到 sku 表中
                    $skuItem = $this->productSkuModel->create($item);
                    $arr = [
                        'sku_id' => $skuItem->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    array_walk($item['ids'], function (&$value, $key, $arr) {
                        $value = array_merge($value, $arr);
                    }, $arr);
                    // 同步数据到 arrtibute_sku_options 表中
                    ProductAttributeSkuOption::insert($item['ids']);
                }
            }
            if (!empty($skuIds)) {
                // 如果有需要删除的 sku，则进行删除
                $this->productSkuModel->whereIn('id', $skuIds)->delete();
                // 删除掉 「sku-属性-属性选项关联表」中的数据
                ProductAttributeSkuOption::whereIn('sku_id', $skuIds)->delete();
            }
            // 同步 sku 最低价格和 sku 总库存到 spu 表中（需要重新查询一次 sku 表，以便获取准备的数据）
            $this->model->find($request->spu->id)->updateLowestPriceOrStock();
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw new ApiException(Code::ERR_QUERY);
        }

    }

    /**
     * 拼接 sku 表中和 arrtibute_sku_options 表中所需插入的数据
     * 并检查 sku 模板数据的合法性
     *
     * @param $request
     * @return array
     * @throws ApiException
     */
    private function preAttrSkuOptData($request) : array
    {
        $skusParams = $request->skus;  // 当前提交的 sku 参数
        if (empty($skusParams)) {
            throw new ApiException(Code::ERR_PARAMS, [], '参数不能为空或参数错误');
        }

        // 当前商品所有的属性 id
        $attrIds = $request->spu->attrs->pluck('id')->toArray();
        // 当前商品所有的属性选项 id
        $attrOptIds = $this->productAttributeOptionModel
            ->select('id')
            ->whereIn('attribute_id', $attrIds)
            ->pluck('id')
            ->toArray();

        $spuId = $request->spu->id;  // 当前的商品 id
        $data = [];
        foreach ($skusParams as $k => $v) {
            if (!isset($v['price']) || !is_numeric($v['price'])) {
                throw new ApiException(Code::ERR_PARAMS, [], '商品价格不能为空或格式错误');
            }
            if (!isset($v['stock']) || !is_numeric($v['stock'])) {
                throw new ApiException(Code::ERR_PARAMS, [], '库存不能为空或格式错误');
            }
            if (!isset($v['sku_id']) || !is_numeric($v['sku_id'])) {
                throw new ApiException(Code::ERR_PARAMS, [], 'sku标识不能为空或格式错误');
            }
            $data[$k]['key_attr_option'] = trim($v['ids_str']);
            $data[$k]['price'] = floatval($v['price']);
            $data[$k]['stock'] = intval($v['stock']);
            $data[$k]['main_url'] = trim($v['main_url']);
            $data[$k]['spu_id'] = $spuId;
            $data[$k]['sku_id'] = intval($v['sku_id']);

            $idsArr = str_explode($v['ids_str'], '|');
            if (count($idsArr) != count($attrIds)) {
                throw new ApiException(Code::ERR_PARAMS, [], '提交的属性参数错误');
            }
            $pasoData = [];
            $item = [];
            foreach ($idsArr as $kk => $vv) {
                try {
                    list($attId, $optionId) = str_explode($vv, '_');
                } catch (\Exception $exception) {
                    throw new ApiException(Code::ERR_PARAMS, [], '提交的属性选项参数错误');
                }
                // 检查属性和属性选项的有效性
                if (!in_array(intval($attId), $attrIds) || !in_array(intval($optionId), $attrOptIds)) {
                    throw new ApiException(Code::ERR_PARAMS, [], '属性或者属性选项不合法');
                }
                $item['attribute_id'] = intval($attId);
                $item['option_id'] = intval($optionId);
                $pasoData[] = $item;
            }
            $data[$k]['ids'] = $pasoData;
        }
        return $data;
    }

}

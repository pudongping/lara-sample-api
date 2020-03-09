<?php
/**
 * 商品相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/4
 * Time: 23:39
 */

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Admin\Product\ProductSpuRepository;
use App\Http\Requests\Admin\Product\ProductSpuRequest;
use App\Models\Product\ProductSpu;

class ProductSpuController extends Controller
{

    protected $productSpuRepository;

    public function __construct(ProductSpuRepository $productSpuRepository)
    {
        $this->init();
        $this->productSpuRepository = $productSpuRepository;
    }

    /**
     * 商品列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->productSpuRepository->getList($request);
        return $this->response->send($data);
    }

    /**
     * 添加主商品
     *
     * @param ProductSpuRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function store(ProductSpuRequest $request)
    {
        $data = $this->productSpuRepository->storage($request);
        return $this->response->send($data);
    }

    /**
     * 编辑显示主商品
     *
     * @param ProductSpu $spu
     * @return mixed
     */
    public function edit(ProductSpu $spu)
    {
        return $this->response->send($spu);
    }

    /**
     * 编辑主商品-数据提交
     *
     * @param ProductSpuRequest $request
     * @param ProductSpu $spu
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function update(ProductSpuRequest $request, ProductSpu $spu)
    {
        $data = $this->productSpuRepository->modify($request);
        return $this->response->send($data);
    }

    /**
     * 商品详情（查看商品描述信息）
     *
     * @param ProductSpu $spu
     * @return mixed
     */
    public function show(ProductSpu $spu)
    {
        return $this->response->send($spu);
    }

    /**
     * 更新商品详情数据提交
     *
     * @param ProductSpuRequest $request
     * @param ProductSpu $spu
     * @return mixed
     */
    public function modifyDescription(ProductSpuRequest $request, ProductSpu $spu)
    {
        $data = $this->productSpuRepository->update($spu->id, $request->only(['description']));
        return $this->response->send($data);
    }

    /**
     * 获取 sku 模板数据
     *
     * @param Request $request
     * @param ProductSpu $spu
     * @return mixed
     */
    public function getSkusTemplate(Request $request, ProductSpu $spu)
    {
        $data = $this->productSpuRepository->getSkusTemplate($request);
        return $this->response->send($data);
    }

    /**
     * 添加 「属性-属性选项值」 或者 更新 「属性-属性选项值」
     *
     * @param Request $request
     * @param ProductSpu $spu
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function attrOptStoreOrUpdate(Request $request, ProductSpu $spu)
    {
        $data = $this->productSpuRepository->attrOptStoreOrUpdate($request);
        return $this->response->send($data);
    }

    /**
     * 新建 sku 或者 更新 sku 数据
     *
     * @param Request $request
     * @param ProductSpu $spu
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function skuStoreOrUpdate(Request $request, ProductSpu $spu)
    {
        $data = $this->productSpuRepository->skuStoreOrUpdate($request);
        return $this->response->send($data);
    }

}

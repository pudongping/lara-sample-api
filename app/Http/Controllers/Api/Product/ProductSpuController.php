<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Api\Product\ProductSpuRepository;
use App\Repositories\Admin\Product\ProductBrandRepository;
use App\Repositories\Admin\Product\ProductCategoryRepository;
use App\Models\Product\ProductSpu;

class ProductSpuController extends Controller
{

    protected $productSpuRepository;
    protected $productBrandRepository;
    protected $productCategoryRepository;

    public function __construct(
        ProductSpuRepository $productSpuRepository,
        ProductBrandRepository $productBrandRepository,
        ProductCategoryRepository $productCategoryRepository
    ) {
        $this->init();
        $this->productSpuRepository = $productSpuRepository;
        $this->productBrandRepository = $productBrandRepository;
        $this->productCategoryRepository = $productCategoryRepository;
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
     * 类目树型结构
     *
     * @return mixed
     */
    public function allCateTree()
    {
        $data = $this->productCategoryRepository->allCateTree();
        return $this->response->send($data);
    }

    /**
     * 所有的品牌
     *
     * @return mixed
     */
    public function allBrands()
    {
        $data = $this->productBrandRepository->allBrands();
        return $this->response->send($data);
    }

    /**
     * 商品详情
     *
     * @param Request $request
     * @return mixed
     */
    public function detail(Request $request)
    {
        $data = $this->productSpuRepository->detail($request);
        return $this->response->send($data);
    }

    /**
     *  收藏商品
     *
     * @param ProductSpu $product
     * @param Request $request
     * @return mixed
     */
    public function favor(ProductSpu $product, Request $request)
    {
        $data = $this->productSpuRepository->favor($request);
        return $this->response->send($data);
    }

    /**
     * 取消收藏商品
     *
     * @param ProductSpu $product
     * @param Request $request
     * @return mixed
     */
    public function disfavor(ProductSpu $product, Request $request)
    {
        $data = $this->productSpuRepository->disfavor($request);
        return $this->response->send($data);
    }

    /**
     * 收藏商品列表
     *
     * @param Request $request
     * @return mixed
     */
    public function favorites(Request $request)
    {
        $data = $this->productSpuRepository->favorites($request);
        return $this->response->send($data);
    }

}

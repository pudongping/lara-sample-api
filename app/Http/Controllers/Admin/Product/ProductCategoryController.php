<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\ProductCategory;
use App\Repositories\Admin\Product\ProductCategoryRepository;
use App\Http\Requests\Admin\Product\ProductCategoryRequest;

class ProductCategoryController extends Controller
{

    protected $productCategoryRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository)
    {
        $this->init();
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * 类目列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->productCategoryRepository->getList($request);
        return $this->response->send($data);
    }

    /**
     * 类目树型结构
     *
     * @return mixed
     */
    public function categoryTree()
    {
        $data = $this->productCategoryRepository->categoryTree();
        return $this->response->send($data);
    }

    /**
     * 添加类目
     *
     * @param ProductCategoryRequest $request
     * @return mixed
     */
    public function store(ProductCategoryRequest $request)
    {
        $data = $this->productCategoryRepository->storage($request);
        return $this->response->send($data);
    }

    /**
     * 编辑显示类目
     *
     * @param ProductCategory $category
     * @return mixed
     */
    public function edit(ProductCategory $category)
    {
        return $this->response->send($category);
    }

    /**
     * 编辑类目-数据处理
     *
     * @param ProductCategoryRequest $request
     * @param ProductCategory $category
     * @return mixed
     */
    public function update(ProductCategoryRequest $request, ProductCategory $category)
    {
        $data = $this->productCategoryRepository->modify($request);
        return $this->response->send($data);
    }

    /**
     * 删除类目
     *
     * @param ProductCategory $category
     * @return mixed
     * @throws \Exception
     */
    public function destroy(ProductCategory $category)
    {
        $category->delete();
        return $this->response->send();
    }

}

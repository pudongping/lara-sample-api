<?php
/**
 * 商品品牌相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/2
 * Time: 22:38
 */

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Admin\Product\ProductBrandRepository;
use App\Http\Requests\Admin\Product\ProductBrandRequest;
use App\Models\Product\ProductBrand;

class ProductBrandController extends Controller
{

    protected $productBrandRepository;

    public function __construct(ProductBrandRepository $productBrandRepository)
    {
        $this->init();
        $this->productBrandRepository = $productBrandRepository;
    }

    /**
     * 品牌列表
     *
     * @param Request $request
     * @return mixed
     */
     public function index(Request $request)
     {
         $data = $this->productBrandRepository->getList($request);
         return $this->response->send($data);
     }

    /**
     * 新建品牌
     *
     * @param ProductBrandRequest $request
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
     public function store(ProductBrandRequest $request)
     {
         $data = $this->productBrandRepository->storage($request);
         return $this->response->send($data);
     }

    /**
     * 品牌编辑显示
     *
     * @param ProductBrand $brand
     * @return mixed
     */
     public function edit(ProductBrand $brand)
     {
         return $this->response->send($brand);
     }

    /**
     * 品牌编辑-数据提交
     *
     * @param ProductBrandRequest $request
     * @param ProductBrand $brand
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
     public function update(ProductBrandRequest $request, ProductBrand $brand)
     {
         $data = $this->productBrandRepository->modify($request);
         return $this->response->send($data);
     }

    /**
     * 删除品牌
     *
     * @param ProductBrand $brand
     * @return mixed
     * @throws \Exception
     */
     public function destroy(ProductBrand $brand)
     {
         $brand->delete();
         $brand->categories()->detach();
         return $this->response->send();
     }

}

<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductSku;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Product\CartRequest;
use App\Repositories\Api\Product\CartRepository;

class CartController extends Controller
{

    protected $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->init();
        $this->cartRepository = $cartRepository;
    }

    /**
     * 购物车列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        // with(['productSku.spu']) 预加载多层级的关联关系
        $data = $request->user()->cartItems()->with(['productSku.spu'])->get()->toArray();
        return $this->response->send($data);
    }

    /**
     * 加入购物车
     *
     * @param CartRequest $request
     * @return mixed
     */
    public function store(CartRequest $request)
    {
        $data = $this->cartRepository->storage($request);
        return $this->response->send($data);
    }

    /**
     * 移除购物车中的商品
     *
     * @param ProductSku $sku
     * @param Request $request
     * @return mixed
     */
    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where('sku_id', $sku->id)->delete();
        return $this->response->send();
    }


}

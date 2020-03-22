<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
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


}

<?php
/**
 * 购物车相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/22
 * Time: 1:52
 */

namespace App\Repositories\Api\Product;

use App\Repositories\BaseRepository;
use App\Models\Product\CartItem;

class CartRepository extends BaseRepository
{

    protected $model;

    public function __construct(CartItem $cartItem)
    {
        $this->model = $cartItem;
    }

    /**
     * 加入购物车
     *
     * @param $request
     */
    public function storage($request)
    {
        $user = $request->user();
        // 从数据库中查询该商品是否已经在购物车中
        $cart = $user->cartItems()->where('sku_id', $request->sku_id)->first();
        if ($cart) {
            // 如果存在则直接叠加商品数量
            $cart->update(['amount' => $cart->amount + $request->amount]);
        } else {
            $input = [
                'amount' => $request->amount,
                'user_id' => $user->id,
                'sku_id' => $request->sku_id
            ];
            $this->store($input);
        }
    }

}

<?php

namespace App\Models\Order;

use App\Models\Model;
use App\Models\Product\ProductSpu;
use App\Models\Product\ProductSku;

class OrderItem extends Model
{
    protected $fillable = ['amount', 'price', 'rating', 'review', 'reviewed_at'];

    protected $dates = ['reviewed_at'];

    public $timestamps = false;

    /**
     * 一个商品有多条订单记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function spu()
    {
        return $this->belongsTo(ProductSpu::class, 'spu_id', 'id');
    }

    /**
     * 一个 sku 有多条订单记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id', 'id');
    }

    /**
     * 一个订单拥有多条商品组成的订单记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

}

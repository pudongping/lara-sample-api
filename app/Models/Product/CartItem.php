<?php

namespace App\Models\Product;

use App\Models\Model;
use App\Models\Auth\User;

class CartItem extends Model
{

    protected $fillable = ['user_id', 'sku_id', 'amount'];

    /**
     * 一个用户拥有多条购物车记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 购物车和 sku 多对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id', 'id');
    }


}

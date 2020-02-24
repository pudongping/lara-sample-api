<?php
/**
 * 商品相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/24
 * Time: 00:00
 */

namespace App\Models\Product;

use App\Models\Model;

class Product extends Model
{

    protected $fillable = [
        'title', 'description', 'image', 'on_sale', 'rating',
        'sold_count', 'review_count', 'price'
    ];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];

    /**
     * 商品和商品 sku 一对多关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skus()
    {
        return $this->hasMany(ProductSku::class, 'product_id', 'id');
    }


}

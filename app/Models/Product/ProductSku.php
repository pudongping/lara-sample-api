<?php
/**
 * 商品 SKU 相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/24
 * Time: 09:40
 */

namespace App\Models\Product;

use App\Models\Model;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description', 'price', 'stock'];

    /**
     * 商品 SKU 和 商品的多对一关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id', 'product_id');
    }

}

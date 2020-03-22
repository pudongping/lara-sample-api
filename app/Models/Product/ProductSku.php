<?php

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSku extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'spu_id', 'name', 'description', 'main_url', 'price',
        'stock', 'code', 'barcode', 'key_attr_option'
    ];

    /**
     * 一个 sku 对应一个 spu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function spu()
    {
        return $this->belongsTo(ProductSpu::class, 'spu_id', 'id');
    }


}

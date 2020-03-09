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


}

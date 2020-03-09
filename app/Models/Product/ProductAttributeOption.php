<?php

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeOption extends Model
{

    use SoftDeletes;

    protected $fillable = ['attribute_id', 'name', 'sort'];

    /**
     * 排序-本地作用域
     *
     * @param $query
     * @return mixed
     */
    public function scopeDataSort($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

}

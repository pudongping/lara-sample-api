<?php

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{

    use SoftDeletes;

    protected $fillable = ['spu_id', 'name', 'sort'];

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

    /**
     * 销售属性和销售属性选项-一对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributeOptions()
    {
        return $this->hasMany(ProductAttributeOption::class, 'id', 'attribute_id');
    }

}

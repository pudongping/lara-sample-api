<?php
/**
 * 商品品牌相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/2
 * Time: 22:38
 */

namespace App\Models\Product;

use App\Models\Model;

class ProductBrand extends Model
{

    protected $fillable = ['name', 'description', 'log_url', 'sort', 'status'];

    const STATUS_ENABLE = 1;
    const STATUS_UNABLE = 0;

    public static $statusMsg = [
        self::STATUS_ENABLE => '启用',
        self::STATUS_UNABLE => '禁用'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * 状态获取器
     *
     * @param $value
     * @return mixed
     */
    public function getStatusAttribute($value)
    {
        return static::$statusMsg[intval($value)] ?? static::$statusMsg[self::STATUS_ENABLE];
    }

    /**
     * 多对多关系-品牌和类目
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'product_categories_pivot_brands', 'brand_id', 'category_id')->withTimestamps();
    }

}

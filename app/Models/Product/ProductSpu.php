<?php
/**
 * 商品相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/4
 * Time: 23:00
 */

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSpu extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'category_ids', 'brand_id', 'title', 'unit', 'sketch',
        'keywords', 'tags', 'barcode', 'price', 'market_price',
        'rating', 'sold_count', 'review_count', 'virtual_retail_num', 'description',
        'stock', 'warning_stock', 'main_image', 'slider_image', 'status', 'sort'
    ];

    const STATUS_UN_PUTWAY = 1;
    const STATUS_PUTWAY = 2;
    const STATUS_SOLD_OUT = 3;
    const STATUS_PRE_SALE = 4;

    public static $statusMsg = [
        self::STATUS_UN_PUTWAY => '未上架',
        self::STATUS_PUTWAY => '上架',
        self::STATUS_SOLD_OUT => '下架',
        self::STATUS_PRE_SALE => '预售'
    ];

    protected $casts = [
        'category_ids' => 'array',
    ];

    /**
     * 状态获取器
     *
     * @param $value
     * @return mixed
     */
    public function getStatusAttribute($value)
    {
        return static::$statusMsg[intval($value)] ?? static::$statusMsg[self::STATUS_UN_PUTWAY];
    }

    /**
     * 获取状态的原始值
     *
     * @return mixed
     */
    public function getOriginalStatusAttribute()
    {
        return $this->attributes['status'];
    }

    /**
     * 定义轮播图-修改器
     *
     * @param $value
     */
    public function setSliderImageAttribute($value)
    {
        $this->attributes['slider_image'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 定义轮播图-访问器
     *
     * @param $value
     * @return mixed
     */
    public function getSliderImageAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 定义分类数组-修改器
     *
     * @param $value
     */
    public function setCategoryIdsAttribute($value)
    {
        $this->attributes['category_ids'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 定义分类数组-访问器
     *
     * @param $value
     * @return mixed
     */
    public function getCategoryIdsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 只允许特定状态的商品被访问
     *
     * @param $query
     * @return mixed
     */
    public function scopeAllowStatus($query)
    {
        return $query->where('status', self::STATUS_PUTWAY);
    }

    /**
     * 商品和类目-多对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'product_categories_pivot_spus', 'spu_id', 'category_id')->withTimestamps();
    }

    /**
     * 商品和品牌-一对一关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function brand()
    {
        return $this->hasOne(ProductBrand::class, 'id', 'brand_id');
    }

    /**
     * 商品和销售属性-一对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attrs()
    {
        return $this->hasMany(ProductAttribute::class, 'spu_id', 'id');
    }

    /**
     * 商品和 sku -一对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skus()
    {
        return $this->hasMany(ProductSku::class, 'spu_id', 'id');
    }

    /**
     * 同步 sku 最低价格和 sku 总库存到 spu 表中
     */
    public function updateLowestPriceOrStock()
    {
        $skus = $this->skus->toArray();
        $minPrice = collect(array_column($skus, 'price'))->min();  // 计算所有 sku 中的最低价格
        $totalStock = collect(array_column($skus, 'stock'))->sum();  // 计算所有 sku 的总库存
        $this->price = $minPrice;
        $this->stock = $totalStock;
        $this->save();
    }


}


<?php
/**
 * 商品类目
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/1
 * Time: 21:22
 */

namespace App\Models\Product;

use App\Models\Model;

class ProductCategory extends Model
{
    protected $fillable = ['pid', 'name', 'description', 'sort', 'status', 'level', 'path'];

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
     * 一对多关联-一个类目允许有多个子类目
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'pid', 'id');
    }

    /**
     * 一对多反向关联-一个类目可能含有多个层级的类目（子有父，父有父，父再有父）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'pid', 'id');
    }

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
     * 获取所有祖先类目的 ID 数组
     *
     * @return array
     */
    public function getPathIdsAttribute()
    {
        return str_explode($this->path, '-');
    }

    /**
     * 获取所有祖先类目并按层级排序
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAttribute()
    {
        return ProductCategory::query()
            // 使用上面的访问器获取所有祖先类目 ID
            ->whereIn('id', $this->path_ids)
            ->orderBy('level')
            ->get();
    }

    /**
     * 获取以 - 为分隔的所有祖先类目名称以及当前类目的名称
     *
     * @return mixed
     */
    public function getFullNameAttribute()
    {
        return $this->ancestors  // 获取所有祖先类目
                    ->pluck('name') // 取出所有祖先类目的 name 字段作为一个数组
                    ->push($this->name) // 将当前类目的 name 字段值加到数组的末尾
                    ->implode(' - '); // 用 - 符号将数组的值组装成一个字符串
    }

    /**
     * 只允许访问 「启用」状态的类目
     *
     * @param $query
     * @return mixed
     */
    public function scopeAllowStatus($query)
    {
        return $query->where('status', self::STATUS_ENABLE);
    }

}

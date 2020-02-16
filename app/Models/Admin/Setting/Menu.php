<?php

namespace App\Models\Admin\Setting;

use App\Models\Model;

class Menu extends Model
{

    const STATE_NORMAL = 1;  // 显示
    const STATE_DENY = 0;  // 隐藏
    const TYPE_BEHIND = 1;  // 后端
    const TYPE_FRONT = 0;  // 前端


    /**
     * 菜单状态
     *
     * @var array
     */
    public static $state = [
        self::STATE_NORMAL => '显示',
        self::STATE_DENY => '隐藏'
    ];

    public static $type = [
        self::TYPE_BEHIND => '后端',
        self::TYPE_FRONT => '前端',
    ];

    protected $fillable = [
        'pid', 'route_name', 'cn_name', 'permission', 'icon', 'extra', 'description', 'sort', 'state', 'type'
    ];

    public function getStateAttribute($value)
    {
        return self::$state[$value] ?? self::$state[self::STATE_NORMAL];  // 默认为：显示
    }

    public function getTypeAttribute($value)
    {
        return self::$type[$value] ?? self::$type[self::TYPE_FRONT];  // 默认为：前端
    }

}

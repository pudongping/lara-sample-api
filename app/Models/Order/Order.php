<?php

namespace App\Models\Order;

use App\Models\Model;
use App\Models\Auth\User;

class Order extends Model
{
    /**
     * 退货状态
     */
    const REFUND_STATUS_PENDING = 1;
    const REFUND_STATUS_APPLIED = 2;
    const REFUND_STATUS_PROCESSING = 3;
    const REFUND_STATUS_SUCCESS = 4;
    const REFUND_STATUS_FAILED = 5;

    /**
     * 物流状态
     */
    const SHIP_STATUS_PENDING = 1;
    const SHIP_STATUS_DELIVERED = 2;
    const SHIP_STATUS_RECEIVED = 3;

    /**
     * 支付方式
     */
    const PAYMENT_METHOD_ALIPAY = 1;
    const PAYMENT_METHOD_WECHAT = 2;

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_APPLIED    => '已申请退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
    ];

    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING   => '未发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED  => '已收货',
    ];

    public static $paymentMethodMap = [
        self::PAYMENT_METHOD_ALIPAY => '支付宝',
        self::PAYMENT_METHOD_WECHAT => '微信',
    ];

    protected $fillable = [
        'order_no', 'address', 'total_amount', 'remark', 'payment_method', 'payment_no',
        'refund_status', 'refund_no', 'is_closed', 'is_reviewed', 'ship_status', 'ship_data', 'extra', 'paid_at'
    ];

    protected $casts = [
        'is_closed'    => 'boolean',
        'is_reviewed'  => 'boolean',
        'address'   => 'json',
        'ship_data' => 'json',
        'extra'     => 'json',
    ];

    protected $dates = [
        'paid_at',
    ];

    /**
     * 一个用户拥有多个订单
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 一个订单有多个 sku 关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /**
     * 生成订单流水号
     *
     * @return bool|string  生成成功则返回订单流水号，否则则返回 false
     * @throws \Exception
     */
    public static function findAvailableOrderNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < config('api.order.make_order_no_times'); $i++) {
            // 随机生成 6 位数字
            $orderNo = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在相同的订单号
            if (!static::query()->where('order_no', $orderNo)->exists()) return $orderNo;
        }
        \Log::warning('订单流水号生成失败');
        return false;
    }


}

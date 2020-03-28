<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/23
 * Time: 23:49
 */

namespace App\Observers\Order;

use App\Models\Order\Order;

class OrderObserver
{

    /**
     * 在写入数据库之前触发
     *
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function creating(Order $order)
    {
        // 如果模型中的 order_no 字段为空
        if (!$order->order_no) {
            // 则生成订单流水号
            $order->order_no = Order::findAvailableOrderNo();
            // 如果订单流水号生成失败，则直接终止创建订单（这种情况少之又少）
            if (!$order->order_no) return false;
        }

    }


}

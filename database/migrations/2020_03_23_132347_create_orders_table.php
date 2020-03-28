<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('下单的用户 id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_no')->unique()->comment('订单流水号');
            $table->text('address')->comment('json 格式的收货地址');
            $table->decimal('total_amount', 10, 2)->comment('订单总金额');
            $table->text('remark')->nullable()->comment('订单备注信息');
            $table->unsignedTinyInteger('payment_method')->default(0)->comment('支付方式：1=支付宝，2=微信');
            $table->string('payment_no')->nullable()->comment('支付平台订单号');
            $table->unsignedTinyInteger('refund_status')->default(1)->comment('退款状态：1=未退款，2=已申请退款，3=退款中，4=退款成功，5=退款失败');
            $table->string('refund_no')->unique()->nullable()->comment('退款单号');
            $table->boolean('is_closed')->default(false)->comment('订单是否已关闭');
            $table->boolean('is_reviewed')->default(false)->comment('订单是否已评价');
            $table->unsignedTinyInteger('ship_status')->default(1)->comment('物流状态：1=未发货，2=已发货，3=已收货');
            $table->text('ship_data')->nullable()->comment('物流数据');
            $table->text('extra')->nullable()->comment('其他额外的数据');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `orders` COMMENT='订单表'");

        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->comment('所属订单 id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('spu_id')->comment('商品 id');
            $table->unsignedBigInteger('sku_id')->comment('sku 的 id');
            $table->unsignedInteger('amount')->comment('数量');
            $table->decimal('price', 10, 2)->comment('单价');
            $table->unsignedInteger('rating')->nullable()->comment('用户打分');
            $table->text('review')->nullable()->comment('用户评价');
            $table->timestamp('reviewed_at')->nullable()->comment('评价时间');
        });
        DB::statement("ALTER TABLE `order_items` COMMENT='订单附属表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
}

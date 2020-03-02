<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique()->default('')->comment('品牌名称');
            $table->string('description')->default('')->comment('品牌描述信息');
            $table->string('log_url')->default('')->comment('品牌 log 的 url');
            $table->tinyInteger('status')->default(1)->comment('状态：1=启用，0=禁用');
            $table->unsignedInteger('sort')->default(0)->comment('排序编号，默认为 0');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_brands` COMMENT='商品品牌表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_brands');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pid')->index()->default(0)->comment('父级类目 id');
            $table->string('name')->default('')->comment('类目名称');
            $table->string('description')->default('')->comment('类目描述信息');
            $table->unsignedInteger('sort')->default(0)->comment('排序编号，默认为 0');
            $table->tinyInteger('status')->default(1)->comment('状态：1=启用，0=禁用');
            $table->unsignedInteger('level')->default(0)->comment('当前类目层级，默认为 0');
            $table->string('path')->default('')->comment('当前类目所有父类目 id');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_categories` COMMENT='商品类目表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}

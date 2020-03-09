<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductSpusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_spus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('category_ids')->index()->default('')->comment('分类id，比如：1,2,3');
            $table->unsignedInteger('brand_id')->default(0)->comment('品牌id');
            $table->string('title')->default('')->comment('商品标题');
            $table->string('unit', 20)->default('')->comment('单位');
            $table->string('sketch')->default('')->comment('简述');
            $table->string('keywords')->index()->default('')->comment('搜索关键字，多个用|分隔');
            $table->string('tags')->default('')->comment('标签，多个用|分隔');
            $table->string('barcode', 80)->default('')->comment('仓库条码');
            $table->decimal('price', 10, 2)->index()->comment('商品最低价');
            $table->decimal('market_price', 10, 2)->comment('市场价格');
            $table->float('rating')->default(5)->comment('商品平均评分');
            $table->unsignedInteger('sold_count')->default(0)->comment('累计销量');
            $table->unsignedInteger('review_count')->default(0)->comment('累计评价');
            $table->unsignedInteger('virtual_retail_num')->default(0)->comment('虚拟购买量');
            $table->text('description')->nullable()->comment('商品详情描述');
            $table->unsignedInteger('stock')->default(0)->comment('商品库存总量');
            $table->unsignedSmallInteger('warning_stock')->default(0)->comment('库存警告数量');
            $table->string('main_image')->default('')->comment('商品介绍主图 url');
            $table->string('slider_image', 2000)->default('')->comment('封面轮播图 url，json 字符串');
            $table->unsignedTinyInteger('status')->default(1)->comment('商品状态：1=未上架，2=上架，3=下架，4=预售');
            $table->unsignedMediumInteger('sort')->index()->default(0)->comment('排序编号');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_spus` COMMENT='商品主表'");

        Schema::create('product_categories_pivot_spus', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->comment('类目表id');
            $table->unsignedBigInteger('spu_id')->comment('商品id');
            $table->timestamps();
            $table->index(['category_id', 'spu_id']);
        });
        DB::statement("ALTER TABLE `product_categories_pivot_spus` COMMENT='商品主表和商品分类表多对多关联表'");

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('spu_id')->comment('商品id');
            $table->string('name', 40)->default('')->comment('规格名称');
            $table->unsignedMediumInteger('sort')->default(0)->comment('排序编号');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_attributes` COMMENT='商品销售属性表'");

        Schema::create('product_attribute_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attribute_id')->comment('属性id');
            $table->string('name', 40)->default('')->comment('属性值名称');
            $table->unsignedMediumInteger('sort')->default(0)->comment('排序编号');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_attribute_options` COMMENT='商品销售属性选项值表'");

        Schema::create('product_skus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('spu_id')->comment('商品id');
            $table->string('name', 40)->default('')->comment('sku 名称');
            $table->string('description')->default('')->comment('sku 描述信息');
            $table->string('main_url')->default('')->comment('主图');
            $table->decimal('price', 10, 2)->index()->comment('价格');
            $table->unsignedInteger('stock')->default(0)->index()->comment('sku 库存');
            $table->string('code')->default('')->comment('商品编码');
            $table->string('barcode')->default('')->comment('商品条形码');
            $table->string('key_attr_option')->default('')->comment('销售属性和销售属性值，比如：颜色-黑色|尺寸-xl');
            $table->index(['name', 'spu_id']);
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_skus` COMMENT='商品 sku 表'");

        Schema::create('product_attribute_sku_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sku_id')->comment('sku 表的 id');
            $table->unsignedBigInteger('attribute_id')->comment('属性表的id');
            $table->unsignedBigInteger('option_id')->comment('属性选项表的id');
            $table->index(['sku_id', 'attribute_id', 'option_id'], 'paao_sku_id_attribute_id_option_id_index');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `product_attribute_sku_options` COMMENT='销售属性、sku、属性值绑定表'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_spus');
        Schema::dropIfExists('product_categories_pivot_spus');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_attribute_options');
        Schema::dropIfExists('product_skus');
        Schema::dropIfExists('product_attribute_sku_options');
    }
}

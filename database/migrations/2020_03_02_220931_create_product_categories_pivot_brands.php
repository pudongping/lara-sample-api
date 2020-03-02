<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductCategoriesPivotBrands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories_pivot_brands', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->comment('类目id');
            $table->unsignedBigInteger('brand_id')->comment('品牌id');
            $table->timestamps();
            $table->index(['category_id', 'brand_id']);
        });
        DB::statement("ALTER TABLE `product_categories_pivot_brands` COMMENT='商品类目表关联商品品牌表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories_pivot_brands');
    }
}

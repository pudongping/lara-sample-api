<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pid')->default(0)->comment('父级id，默认为0，则顶级菜单');
            $table->string('route_name')->default('')->comment('路由名称');
            $table->string('cn_name', 100)->default('')->comment('菜单中文名称');
            $table->string('permission')->default('')->comment('权限，多个权限用|分割');
            $table->string('icon', 100)->default('')->comment('图标');
            $table->text('extra')->nullable()->comment('额外字段');
            $table->text('description')->nullable()->comment('描述');
            $table->smallInteger('sort')->default(0)->comment('排序');
            $table->tinyInteger('state')->default(1)->comment('菜单状态：1=显示，0=隐藏');
            $table->tinyInteger('type')->default(0)->comment('菜单类型：1=后端，0=前端');
            $table->timestamps();
            $table->index(['route_name', 'permission', 'cn_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
}

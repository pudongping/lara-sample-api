<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 150)->unique()->comment('设置指标唯一的 key，比如：site_name');
            $table->text('value')->nullable()->comment('指标值，比如：我的个人网站');
            $table->string('name', 150)->nullable()->comment('指标名称，比如：站点名称');
            $table->string('content', 255)->nullable()->comment('备注信息');
            $table->integer('sort')->default(0)->comment('排序编号');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}

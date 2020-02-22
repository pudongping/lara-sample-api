<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->default(0)->index()->comment('和普通登录方式的关联用户id');;
            $table->tinyInteger('social_type')->default(0)->comment('登录方式：0=normal，1=weixin……');
            $table->string('openid')->unique()->nullable()->comment('当前授权唯一标识');
            $table->string('unionid')->unique()->nullable()->comment('当授权服务为Weixin的时候，可能还需要union_id');
            $table->string('nickname')->default('')->comment('昵称');
            $table->tinyInteger('sex')->nullable()->comment('性别');
            $table->string('language')->default('')->comment('语言');
            $table->string('city')->default('')->comment('城市');
            $table->string('province')->default('')->comment('省份');
            $table->string('country')->default('')->comment('国家');
            $table->string('headimgurl')->default('')->comment('第三方授权服务头像url');
            $table->string('extra')->default('')->comment('其他可能需要保存的数据');
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
        Schema::dropIfExists('social_users');
    }
}

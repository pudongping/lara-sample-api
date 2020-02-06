<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocialiteFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('bound_id')->unsigned()->default(0)->index()->after('id')->comment('和普通登录方式的关联id');;
            $table->tinyInteger('social_type')->default(0)->after('bound_id')->comment('登录方式：0=normal，1=weixin……');
            $table->string('openid')->unique()->nullable()->after('email')->comment('当前授权唯一标识');
            $table->string('unionid')->unique()->nullable()->after('openid')->comment('当授权服务为Weixin的时候，可能还需要union_id');
            $table->string('nickname')->default('')->after('unionid')->comment('昵称');
            $table->tinyInteger('sex')->nullable()->after('nickname')->comment('性别');
            $table->string('language')->default('')->after('sex')->comment('语言');
            $table->string('city')->default('')->after('language')->comment('城市');
            $table->string('province')->default('')->after('city')->comment('省份');
            $table->string('country')->default('')->after('province')->comment('国家');
            $table->string('avatar')->default('')->after('country')->comment('自定义头像');
            $table->string('headimgurl')->default('')->after('avatar')->comment('第三方授权服务头像url');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bound_id');
            $table->dropColumn('social_type');
            $table->dropColumn('openid');
            $table->dropColumn('unionid');
            $table->dropColumn('nickname');
            $table->dropColumn('sex');
            $table->dropColumn('language');
            $table->dropColumn('city');
            $table->dropColumn('province');
            $table->dropColumn('country');
            $table->dropColumn('avatar');
            $table->dropColumn('headimgurl');
            $table->string('password')->nullable(false)->change();
        });
    }
}

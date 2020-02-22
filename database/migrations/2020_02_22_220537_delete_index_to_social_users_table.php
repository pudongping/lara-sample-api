<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteIndexToSocialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_users', function (Blueprint $table) {
            $table->dropUnique('social_users_openid_unique');
            $table->dropUnique('social_users_unionid_unique');
            $table->index('openid');
            $table->index('unionid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('social_users', function (Blueprint $table) {
            $table->unique('openid');
            $table->unique('unionid');
            $table->dropIndex('social_users_openid_index');
            $table->dropIndex('social_users_unionid_index');
        });
    }
}

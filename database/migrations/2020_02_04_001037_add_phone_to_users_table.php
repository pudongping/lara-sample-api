<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->unique()->after('name')->comment('手机号码');
            $table->string('email', 80)->nullable()->change();
            $table->string('name', 80)->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('avatar')->default('')->after('email')->comment('自定义头像');
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
            $table->dropColumn('phone');
            $table->string('email')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->dropColumn('avatar');
        });
    }
}

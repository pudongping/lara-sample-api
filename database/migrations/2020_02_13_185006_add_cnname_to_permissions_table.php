<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCnnameToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('cn_name')->default('')->after('name')->comment('权限中文名称');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('cn_name')->default('')->after('name')->comment('角色中文名称');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('cn_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('cn_name');
        });
    }
}

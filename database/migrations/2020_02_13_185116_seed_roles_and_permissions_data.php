<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Admin;

class SeedRolesAndPermissionsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 需清除缓存，否则会报错
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 先创建权限
        // 管理站点信息的权限
        Permission::create(['name' => 'manage_settings', 'cn_name' => '站点设置', 'guard_name' => 'admin']);
        // 管理用户的权限
        Permission::create(['name' => 'manage_users', 'cn_name' => '管理用户', 'guard_name' => 'admin']);
        // 管理内容的权限
        Permission::create(['name' => 'manage_contents', 'cn_name' => '管理内容', 'guard_name' => 'admin']);

        // 创建角色
        // 创建站长角色，并赋予所有权限
        $founder = Role::create(['name' => 'Administrator', 'cn_name' => '超级管理员', 'guard_name' => 'admin']);
        // 为站长赋予所有权限
        $founder->givePermissionTo(Permission::all());

        // 创建管理员角色，并赋予权限
        $maintainer = Role::create(['name' => 'Maintainer', 'cn_name' => '普通管理员', 'guard_name' => 'admin']);
        $maintainer->givePermissionTo('manage_contents');

        $modelType = get_class( new Admin());
        // 给 1、2 号用户分别赋予站长权限、管理员权限
        $data = [
            ['model_id' => 1, 'model_type' => $modelType, 'role_id' => $founder->id],
            ['model_id' => 2, 'model_type' => $modelType, 'role_id' => $maintainer->id],
        ];

        DB::table('model_has_roles')->insert($data);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 需清除缓存，否则会报错
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 清空所有数据表数据
        $tableNames = config('permission.table_names');

        Model::unguard();
        DB::table($tableNames['role_has_permissions'])->delete();
        DB::table($tableNames['model_has_roles'])->delete();
        DB::table($tableNames['model_has_permissions'])->delete();
        DB::table($tableNames['roles'])->delete();
        DB::table($tableNames['permissions'])->delete();
        Model::reguard();
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class SeedRolesAndPermissionsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // 清除缓存
        app()['cache']->forget('spatie.permission.cache');

        // 先创建权限
        Permission::create(['name' => 'manage_administrator','display_name'=>'管理员管理']);
        Permission::create(['name' => 'manage_user','display_name'=>'用户管理（导入/导出）']);
        Permission::create(['name' => 'manage_phone_book','display_name'=>'电话簿管理']);
        Permission::create(['name' => 'manage_device','display_name'=>'设备及设备借用管理']);
        Permission::create(['name' => 'manage_activity','display_name'=>'活动管理']);
        Permission::create(['name'=>'manage_service','display_name'=>'学生服务后台管理']);


        // 创建超级管理员角色，并赋予权限
        $root = Role::create(['name' => 'Root','display_name'=>'超级管理员']);
        $root->givePermissionTo('manage_administrator');
        $root->givePermissionTo('manage_user');
        $root->givePermissionTo('manage_phone_book');
        $root->givePermissionTo('manage_device');
        $root->givePermissionTo('manage_activity');
        $root->givePermissionTo('manage_service');


        //创建站长角色，并赋予权限
        $founder = Role::create(['name' => 'Founder','display_name'=>'站长']);
        $founder->givePermissionTo('manage_user');
        $founder->givePermissionTo('manage_phone_book');
        $founder->givePermissionTo('manage_device');
        $founder->givePermissionTo('manage_activity');
        $founder->givePermissionTo('manage_service');

        // 创建管理员角色，并赋予权限
        $administrator = Role::create(['name' => 'Administrator','display_name'=>'管理']);
        $administrator->givePermissionTo('manage_user');
        $administrator->givePermissionTo('manage_phone_book');
        $administrator->givePermissionTo('manage_device');
        $administrator->givePermissionTo('manage_activity');



        //创建正式角色
        $formal = Role::create(['name'=>'Formal','display_name'=>'正式']);
        //创建试用角色
        $probation = Role::create(['name'=>'Probation','display_name'=>'试用']);
        //创建退站角色
        $secede = Role::create(['name'=>'Secede','display_name'=>'退站']);


        //初始化用户账号密码
        $user_infos = \App\Models\OaYouthUser::all();
        $user = new \App\Models\OaUser();
        $user->username = 'youthol';
        $user->password = bcrypt(env('YOUTHOL_PASSWORD'));
        $user->sdut_id = '00000000000';
        $user->save();
        $user->assignRole('Root');
        $userinfo = new \App\Models\OaYouthUser();
        $userinfo->name = '青春在线';
        $userinfo->department = '青春在线';
        $userinfo->sdut_id = '00000000000';
        $userinfo->save();
        foreach ($user_infos as $userinfo){
            $user = new \App\Models\OaUser();
            $user->username = $userinfo->sdut_id;
            $user->sdut_id = $userinfo->sdut_id;
            $user->password = bcrypt($userinfo->sdut_id);
            $user->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 清除缓存
        app()['cache']->forget('spatie.permission.cache');

        // 清空所有数据表数据
        $tableNames = config('permission.table_names');
        Model::unguard();
        DB::table($tableNames['role_has_permissions'])->delete();
        DB::table($tableNames['model_has_roles'])->delete();
        DB::table($tableNames['model_has_permissions'])->delete();
        DB::table($tableNames['roles'])->delete();
        DB::table($tableNames['permissions'])->delete();
        DB::table('oa_users')->delete();
        Model::reguard();
    }
}

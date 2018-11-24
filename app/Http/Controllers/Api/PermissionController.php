<?php

namespace App\Http\Controllers\Api;

use App\Models\OaUser;
use Illuminate\Http\Request;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    //
    public function index()
    {
        $user = Auth::guard('oa')->user();
        $permission = $user->getAllPermissions();
        return $this->response->array(['data' => $permission])->setStatusCode(200);
    }
    public function role(){
        $role = Role::all();
        foreach ($role as $item) {
            $item->permissions();
        }
        return $this->response->array(['data'=>$role->toArray()]);
    }
    public function permission() {
        $permission = Permission::all();
        return $this->response->array(['data'=>$permission->toArray()]);
    }


    public function assignPermission(Request $request)
    {
        $user = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'role' => 'required|exists:roles,id',
            'permission.*' => 'required|exists:permissions,id'
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('分配权限失败.', $validator->errors());
        }
        if (!$user->hasRole('Root')) {
            return $this->response->error('您没有该权限！', 403);
        }
        $role = Role::findById($request->role);
        foreach ($request->permission as $permission_id) {
//            if ($user->can($permission->name) && $user->can('manager_user') && $permission->name != 'manage_user'){
//
//            }
            $permission = Permission::findById($permission_id);
            if (!$role->hasPermissionTo($permission->name)) {
                //如果角色没有权限，则分配权限
                $role->givePermissionTo($permission->name);
            }
        }
        return $this->response->noContent();
    }

    public function assignRole(Request $request)
    {
        $user = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'user' => 'required|exists:oa_users,sdut_id',
            'role.*' => 'required|exists:roles,id',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('分配角色失败.', $validator->errors());
        }
        //没有操作权限
        if (!$user->can('manage_user') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        $us = OaUser::where('sdut_id', $request->user)->first();
        foreach ($request->role as $role_id) {
            $role = Role::findById($role_id);
            if ($role->name == 'Administrator' && !$user->can('manage_administrator')) {
                return $this->response->error('您无法管理 管理员！', 403);
            }else if($role->name == 'Founder' && !$user->hasRole('Root')){
                return $this->response->error('只有超级管理员才能分配站长！', 403);
            }
            if (!$us->hasRole($role->name)) {
                $us->assignRole($role->name);
            }
        }
        return $this->response->noContent();
    }


}

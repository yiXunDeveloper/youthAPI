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
        $youth_user  = $user->userinfo;
        $permissions = $user->getAllPermissions();
        $role_names = $user->getRoleNames();
        $roles = Role::whereIn('name',$role_names)->get();
        return $this->response->array(['data' => ['userinfo'=>$youth_user,'roles'=>$roles,'permissions'=>$permissions]])->setStatusCode(200);
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
        $permissions = array();
        foreach ($request->permission as $permission_id) {
//            if ($user->can($permission->name) && $user->can('manager_user') && $permission->name != 'manage_user'){
//
//            }
            $permission = Permission::findById($permission_id);
            array_push($permissions,$permission->name);
        }
        $role->syncPermissions($permissions);
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
        if (!$user->can('manage_user') || !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        $us = OaUser::where('sdut_id', $request->user)->first();
        $roles = array();
        foreach ($request->role as $role_id) {
            $role = Role::findById($role_id);
            if ($role->name == 'Administrator' && !$user->can('manage_administrator')) {
                return $this->response->error('您无法管理 管理员！', 403);
            }else if(in_array($role->name,['Founder','Root']) && !$user->hasRole('Root')){
                return $this->response->error('只有超级管理员才能分配站长和超级管理员！', 403);
            }
            array_push($us,$role->name);
        }
        $us->assignRoles($roles);
        return $this->response->noContent();
    }


}

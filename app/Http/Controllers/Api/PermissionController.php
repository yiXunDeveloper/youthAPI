<?php

namespace App\Http\Controllers\Api;

use App\Models\OaEquipmentRecord;
use App\Models\OaSigninDuty;
use App\Models\OaSigninRecord;
use App\Models\OaUser;
use App\Models\OaYouthUser;
use Illuminate\Http\Request;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    //当前登录用户信息
    public function index(){
        $user = Auth::guard('oa')->user();
        $youth_user  = $user->userinfo;
        $permissions = $user->getAllPermissions();
        $role_names = $user->getRoleNames();
        $roles = Role::whereIn('name',$role_names)->get();
        $duty = $youth_user->duty()->first();
        if ($duty) {
            $duty = $duty->duty_at;
        }
        $youth_user->duty_at = $duty;
        return $this->response->array(['data' => ['userinfo'=>$youth_user,'roles'=>$roles,'permissions'=>$permissions]])->setStatusCode(200);
    }
    //根据youth_user id获取用户
    public function getUserById($id) {
        $us = Auth::guard('oa')->user();
        //没有操作权限
//        if (!$us->can('manage_user') || !$us->can('manage_administrator')) {
//            return $this->response->error('您没有该权限！', 403);
//        }
        $youthUser = OaYouthUser::find($id);
        if (!$youthUser) {
            return $this->response->errorNotFound('用户未找到！');
        }
        $user = $youthUser->user()->first();
        $permissions = $user->getAllPermissions();
        $role_names = $user->getRoleNames();
        $roles = Role::whereIn('name',$role_names)->get();
        $duty = $youthUser->duty()->first();
        if ($duty) {
            $duty = $duty->duty_at;
        }
        $youthUser->duty_at = $duty;
        return $this->response->array(['data' => ['userinfo'=>$youthUser,'roles'=>$roles,'permissions'=>$permissions]])->setStatusCode(200);
    }
    public function getAllRoles(){
        $role = Role::all();
        foreach ($role as $item) {
            $item->permissions;
        }
        return $this->response->array(['data'=>$role->toArray()]);
    }
    public function getAllPermissions() {
        $permission = Permission::all();
        return $this->response->array(['data'=>$permission->toArray()]);
    }
    public function getRoleById($id) {
        $role = Role::findById($id);
        if(!$role) {
            return $this->response->errorNotFound('角色未找到');
        }
        $role->permissions;
        return $this->response->array(['data'=>$role])->setStatusCode(200);
    }
    public function getPermissionById($id) {
        $permission = Permission::findById($id);
        if(!$permission) {
            return $this->response->errorNotFound('权限未找到');
        }
        return $this->response->array(['data'=>$permission])->setStatusCode(200);
    }

    //修改角色/给角色分配权限
    public function updateRole($id,Request $request)
    {
        $user = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'display_name' => 'required',
            'permission.*' => 'required|exists:permissions,id'
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('修改角色信息失败.', $validator->errors());
        }
        if (!$user->hasRole('Root')) {
            return $this->response->error('您没有该权限！', 403);
        }
//        $role = Role::findById($request->role);
        $permissions = array();
        foreach ($request->permission as $permission_id) {
//            if ($user->can($permission->name) && $user->can('manager_user') && $permission->name != 'manage_user'){
//
//            }
            $permission = Permission::findById($permission_id);
            array_push($permissions,$permission->name);
        }
        $role = Role::findById($id);
        if(!$role) {
            return $this->response->errorNotFound('角色未找到');
        }
        $role->syncPermissions($permissions);
        $role->display_name = $request->display_name;
        $role->save();
        return $this->response->noContent();
    }

    //修改用户信息/给用户分配角色
    public function updateUser($id,Request $request)
    {
        $user = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'sdut_id' => 'required|size:11',
            'name' => 'required|between:2,10',
            'grade' => 'required|size:4',
            'phone' => 'nullable|size:11',
            'birthday' => 'nullable|date',
            'department' => 'required',
            'role.*' => 'required|exists:roles,id',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('修改用户信息失败.', $validator->errors());
        }
        //没有操作权限
        if (!$user->can('manage_user') || !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        $youthUser = OaYouthUser::find($id);
        if(!$youthUser) {
                return $this->response->errorNotFound('用户未找到');
        }
        $us = $youthUser->user()->first();
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
        if ($request->duty_at) {
            if (sizeof(preg_match("/[0-6]:[1-5]|[0-6]:[1-5]/",$request->duty_at))>0 || sizeof(preg_match("/[0-6]:[1-5]/",$request->duty_at))>0) {
                $duty = OaSigninDuty::where('sdut_id',$us->sdut_id)->first();
                $duty->duty_at = $request->duty_at;
                $duty->save();
            }else{
                return $this->response->error('值班任务不合法',500);
            }
        }
        $us->assignRoles($roles);

        if ($youthUser->sdut_id != $request->sdut_id) {
            OaUser::where('sdut_id',$youthUser->sdut_id)->update([
                'username'=>$request->sdut_id,
                'password'=>bcrypt($request->sdut_id),
                'sdut_id'=>$request->sdut_id,
            ]);
            OaSigninDuty::where('sdut_id',$youthUser->sdut_id)->update([
                'sdut_id'=>$request->sdut_id,
            ]);
            OaSigninRecord::where('sdut_id',$youthUser->sdut_id)->update([
                'sdut_id'=>$request->sdut_id,
            ]);
            OaEquipmentRecord::where('sdut_id',$youthUser->sdut_id)->update([
                'sdut_id'=>$request->sdut_id,
            ]);
            $youthUser->sdut_id = $request->sdut_id;
        }
        $youthUser->name = $request->name;
        $youthUser->grade = $request->grade;
        if ($request->phone) {
            $youthUser->phone = $request->phone;
        }
        if ($request->birthday) {
            $youthUser->birthday = $request->birthday;
        }
        $youthUser->department = $request->department;
        $youthUser->save();
        return $this->response->noContent();
    }
    public function updatePermission($id,Request $request) {
        $validator = app('validator')->make($request->all(), [
            'display_name'=>'required'
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('修改权限信息失败.', $validator->errors());
        }
        $permission = Permission::findById($id);
        if(!$permission) {
            return $this->response->errorNotFound('权限未找到');
        }
        $permission->display_name = $request->display_name;
        return $this->response->noContent();

    }


}

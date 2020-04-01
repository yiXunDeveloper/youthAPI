<?php
/**
 * 办公系统权限相关逻辑
 */

namespace App\Http\Controllers\Api;

use App\Models\OaEquipmentRecord;
use App\Models\OaSigninDuty;
use App\Models\OaSigninRecord;
use App\Models\OaUser;
use App\Models\OaWorkload;
use App\Models\OaYouthUser;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Http\Request;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    //当前登录用户信息
    public function index()
    {
        $user = Auth::guard('oa')->user();
        $youth_user  = $user->userinfo;
        $permissions = $user->getAllPermissions();
        if ($youth_user){
            $duty = $youth_user->duty;
            if ($duty) {
                $duty = $duty->duty_at;
            }
            $youth_user->duty_at = $duty;
        }
        return $this->response->array(['data' => ['userinfo'=>$youth_user,'roles'=>$user->roles,'permissions'=>$permissions]])->setStatusCode(200);
    }

    //添加用户
    public function addUser(Request $request)
    {
        $loginUser = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'sdut_id' => 'required|size:11|unique:oa_users,sdut_id',
            'name' => 'required|between:2,10',
            'grade' => 'required|size:4',
            'phone' => 'nullable|size:11',
            'birthday' => 'nullable|date',
            'department' => 'required',
            'roles' => 'required|array',
            'roles.*' => 'required|exists:roles,id',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('参数错误，添加用户信息失败.', $validator->errors());
        }

        //没有操作权限
        if (!$loginUser->can('manage_user') || !$loginUser->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }

        $roles = array();
        foreach ($request->roles as $role_id) {
            $role = Role::findById($role_id);
            if ($role->name == 'Administrator' && !$loginUser->can('manage_administrator')) {
                return $this->response->error('您无法分配 管理员！', 403);
            }else if(in_array($role->name,['Founder','Root']) && !$loginUser->hasRole('Root')){
                return $this->response->error('只有超级管理员才能分配站长和超级管理员！', 403);
            }
            array_push($roles,$role->name);
        }

        //创建用户
        $user = OaUser::create([
            'username'=>$request->sdut_id,
            'password'=>bcrypt($request->sdut_id),
            'sdut_id'=>$request->sdut_id,
        ]);
        //创建用户信息
        OaYouthUser::create($request->except('roles','duty_at'));

        //传来的参数有值班任务，先判断是否合法，如果合法查询就创建值班任务
        if ($request->duty_at) {
            if (sizeof(preg_match("/[0-6]:[1-5]|[0-6]:[1-5]/",$request->duty_at))>0 || sizeof(preg_match("/[0-6]:[1-5]/",$request->duty_at))>0) {
                $duty = new OaSigninDuty();
                $duty->sdut_id = $request->sdut_id;
                $duty->duty_at = $request->duty_at;
                $duty->save();
            }else{
                return $this->response->error('值班任务不合法',422);
            }
        }
        //返回201状态码
        return $this->response->array(array())->setStatusCode(201);
    }

    //根据youth_user id获取用户
    public function getUserById(OaYouthUser $youthUser) {
        $us = Auth::guard('oa')->user();
        //没有操作权限
        if (!$us->can('manage_user') || !$us->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        if (!$youthUser) {
            return $this->response->errorNotFound('用户未找到！');
        }
        $user = $youthUser->user()->first();
        $permissions = $user->getAllPermissions();
        $user->roles;
        $duty = $youthUser->duty()->first();
        if ($duty) {
            $duty = $duty->duty_at;
        }
        $youthUser->duty_at = $duty;
        return $this->response->array(['data' => ['userinfo'=>$youthUser,'roles'=>$user->roles,'permissions'=>$permissions]])->setStatusCode(200);
    }

    //获取所有角色和权限
    public function getRoles(){
        $role = Role::all();
        foreach ($role as $item) {
            $item->permissions;
        }
        return $this->response->array(['data'=>$role->toArray()]);
    }

    //获取所有权限
    public function getPermissions() {
        $permission = Permission::all();
        return $this->response->array(['data'=>$permission->toArray()]);
    }

    //通过id查找角色
    public function getRoleById(Role $role) {
        if(!$role) {
            return $this->response->errorNotFound('角色未找到');
        }
        $role->permissions;
        return $this->response->array(['data'=>$role])->setStatusCode(200);
    }

    //通过id查找权限
    public function getPermissionById(Permission $permission) {
        if(!$permission) {
            return $this->response->errorNotFound('权限未找到');
        }
        return $this->response->array(['data'=>$permission])->setStatusCode(200);
    }

    public function addRole(Request $request)
    {
        $user = Auth::guard('oa')->user();
        //验证数据
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|unique:roles,name',
            'display_name' => 'required',
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id'
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('修改角色信息失败.', $validator->errors());
        }
        //只有超级管理员才能添加角色
        if (!$user->hasRole('Root')) {
            return $this->response->error('您没有该权限！', 403);
        }
        //通过permission_id分配权限
        $permissions = array();
        foreach ($request->permissions as $permission_id) {
            $permission = Permission::findById($permission_id);
            array_push($permissions,$permission->name);
        }
        $role = Role::create(['name'=>$request->name,'display_name'=>$request->display_name]);
        $role->syncPermissions($permissions);

        return $this->response->noContent()->setStatusCode(201);
    }

    //修改角色/给角色分配权限
    public function updateRole(Role $role,Request $request)
    {
        $user = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'display_name' => 'required',
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id'
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('修改角色信息失败.', $validator->errors());
        }
        if (!$user->hasRole('Root')) {
            return $this->response->error('您没有该权限！', 403);
        }

        $permissions = array();
        foreach ($request->permissions as $permission_id) {
            $permission = Permission::findById($permission_id);
            array_push($permissions,$permission->name);
        }

        if(!$role) {
            return $this->response->errorNotFound('角色未找到');
        }
        $role->syncPermissions($permissions);
        $role->display_name = $request->display_name;
        $role->save();
        return $this->response->noContent();
    }

    //修改用户信息/给用户分配角色
    public function updateUser(OaYouthUser $youthUser,Request $request)
    {
        $user = Auth::guard('oa')->user();
        $validator = app('validator')->make($request->all(), [
            'sdut_id' => 'required|size:11',
            'name' => 'required|between:2,10',
            'grade' => 'required|size:4',
            'phone' => 'nullable|size:11',
            'birthday' => 'nullable|date',
            'department' => 'required',
            'roles' => 'required|array',
            'roles.*' => 'required|exists:roles,id',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('参数错误，修改用户信息失败.', $validator->errors());
        }
        //没有操作权限
        if (!$user->can('manage_user') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        if(!$youthUser) {
                return $this->response->errorNotFound('用户未找到');
        }
        $us = $youthUser->user()->first();

        if($us->hasRole('Admionistrator') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有权限管理 管理员！', 403);
        }
        if($us->hasAnyRole(['Founder','Root']) && !$user->hasRole('Root')) {
            return $this->response->error('您没有权限管理 、站长和超级管理员！', 403);
        }
        $roles = array();
        foreach ($request->roles as $role_id) {
            $role = Role::findById($role_id);
            if ($role->name == 'Administrator' && !$user->can('manage_administrator')) {
                return $this->response->error('您无法分配 管理员！', 403);
            }else if(in_array($role->name,['Founder','Root']) && !$user->hasRole('Root')){
                return $this->response->error('只有超级管理员才能分配站长和超级管理员！', 403);
            }
            array_push($roles,$role->name);
        }
        //传来的参数有值班任务，先判断是否合法，如果合法查询这个人有没有值班任务，没有值班任务就创建，有值班任务就修改
        if ($request->duty_at) {
            if (sizeof(preg_match("/[0-6]:[1-5]|[0-6]:[1-5]/",$request->duty_at))>0 || sizeof(preg_match("/[0-6]:[1-5]/",$request->duty_at))>0) {
                $duty = OaSigninDuty::where('sdut_id',$us->sdut_id)->first();
                if (!$duty) {
                    $duty = new OaSigninDuty();
                    $duty->sdut_id = $us->sdut_id;
                }
                $duty->duty_at = $request->duty_at;
                $duty->save();
            }else{
                return $this->response->error('值班任务不合法',422);
            }
        }else {
            //传来的参数没有值班任务，查询这个人有没有值班任务，若有则删除此任务
            $duty = OaSigninDuty::where('sdut_id',$us->sdut_id)->first();
            if ($duty) {
                $duty->delete();
            }
        }

        $us->syncRoles($roles);

        if ($youthUser->sdut_id != $request->sdut_id) {
            //如果修改学号
//            更新账号密码
            OaUser::where('sdut_id',$youthUser->sdut_id)->update([
                'username'=>$request->sdut_id,
                'password'=>bcrypt($request->sdut_id),
                'sdut_id'=>$request->sdut_id,
            ]);

            //更新值班任务
            OaSigninDuty::where('sdut_id',$youthUser->sdut_id)->update([
                'sdut_id'=>$request->sdut_id,
            ]);
            //更新签到记录
            OaSigninRecord::where('sdut_id',$youthUser->sdut_id)->update([
                'sdut_id'=>$request->sdut_id,
            ]);
            //更新设备借用记录的借用人
            OaEquipmentRecord::where('lend_user',$youthUser->sdut_id)->update([
                'lend_user'=>$request->sdut_id,
            ]);
            //更新设备借用记录的借备忘用人
            OaEquipmentRecord::where('memo_user',$youthUser->sdut_id)->update([
                'memo_user'=>$request->sdut_id,
            ]);
            //更新设备借用记录的归还备忘人
            OaEquipmentRecord::where('rememo_user',$youthUser->sdut_id)->update([
                'rememo_user'=>$request->sdut_id,
            ]);
            //更新工作量
            OaWorkload::where('sdut_id',$youthUser->sdut_id)->update([
                'sdut_id'=>$request->sdut_id,
            ]);
            //更新工作量备忘人
            OaWorkload::where('manager_id',$youthUser->sdut_id)->update([
                'manager_id' => $request->sdut_id,
            ]);


            $youthUser->sdut_id = $request->sdut_id;
        }
        //修改其他信息
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

    public function deleteUser(Request $request,OaYouthUser $youthUser)
    {
        $user = Auth::guard('oa')->user();
        //没有操作权限
        if (!$user->can('manage_user') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        if (!$youthUser) {
            return $this->response->errorNotFound("用户不存在");
        }
        $usered = $youthUser->user()->first();
        $sdut_id = $youthUser->sdut_id;
        if($usered->hasRole('Admionistrator') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有权限管理 【管理员】！', 403);
        }
        if($usered->hasAnyRole(['Founder','Root']) && !$user->hasRole('Root')) {
            return $this->response->error('只有超级管理员才能管理【站长】和【超级管理员】！', 403);
        }
        //删除关联用户信息
        $youthUser->delete();  //OaUser表
        OaSigninDuty::where('sdut_id',$sdut_id)->delete();  //删除对应值班任务
        OaSigninRecord::where('sdut_id',$sdut_id)->delete();  //删除签到记录
        OaWorkload::where('sdut_id',$sdut_id)->delete();  //删除工作量
        $usered->syncRoles([]);
        $usered->delete();  //删除用户
        $this->response->noContent();
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
        $permission->save();
        return $this->response->noContent();

    }


}

<?php

namespace App\Http\Controllers\Api;

use App\Models\PreordainUser;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Hash;

class PreordainController extends Controller
{
    //
    public function login(Request $request){
        $credentials['username'] = $request->username;
        $credentials['password'] = $request->password;
//        $credentials['username'] = $request->username;
//        $credentials['password'] = $request->password;
        /*if (!$user = PreordainUser::where('username',$credentials['username'])->first())
        {
            $user = new PreordainUser();
            $user->username = $credentials['username'];
            $user->password = bcrypt($credentials['password']);
            $user->save();
        }*/
        //找到该用户
        if ($user = PreordainUser::where('username',$credentials['username'])->first())
        {
            //账号密码匹配
            if (Hash::check($credentials['password'],$user->password))
            {
                //用户不是管理员可以登录
               if ($user->admin==0)
               {
                   $token = Auth::guard('preordain')->fromUser($user);
                   return $this->respondWithToken($token)->setStatusCode(201);
               }else
                   //管理员不可登录
               {
                   return $this->response->errorUnauthorized('此用户没有权限');
               }
            }else {
               //账号密码不匹配
                return $this->response->errorUnauthorized('密码错误');
            }
        }else{
            //未找到该用户
            return $this->response->errorUnauthorized('未找到该用户');
        }

    }
    public function adminlogin(Request $request){
        $credentials['username'] = $request->username;
        $credentials['password'] = $request->password;
        /*if (!$user = PreordainUser::where('username',$credentials['username'])->first())
        {
            $user = new PreordainUser();
            $user->username = $credentials['username'];
            $user->password = bcrypt($credentials['password']);
            $user->save();
        }*/
        //找到该用户
        if ($user = PreordainUser::where('username',$credentials['username'])->first())
        {
            //账号密码匹配
            if (Hash::check($credentials['password'],$user->password))
            {
                //用户是管理员才可以登录
                if ($user->admin==1)
                {
                    $token = Auth::guard('preordain')->fromUser($user);
                    return $this->respondWithToken($token)->setStatusCode(201);
                }else
                    //管理员不可登录
                {
                    return $this->response->errorUnauthorized('此用户没有权限');
                }
            }else {
                //账号密码不匹配
                return $this->response->errorUnauthorized('密码错误');
            }
        }else{
            //未找到该用户
            return $this->response->errorUnauthorized('未找到该用户');
        }

    }
    public function userinfo(){
        $user = Auth::guard('preordain')->user();
        return $this->response->array(['name'=>$user->college]);
    }
    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('preordain')->factory()->getTTL() * 60
        ]);
    }
}

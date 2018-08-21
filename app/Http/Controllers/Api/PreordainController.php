<?php

namespace App\Http\Controllers\Api;

use App\Models\PreordainList;
use App\Models\PreordainOpen;
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
                   return $this->respondWithToken($token)->setStatusCode(200);
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
                    return $this->respondWithToken($token)->setStatusCode(200);
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
        return $this->response->array(['data'=>['name'=>$user->name]]);
    }
    public function latestList(){
        $id = PreordainOpen::max('id');
        if(!$id){
            return $this->response->noContent();
        }
        $data = PreordainList::where('order_id',$id)->orderBy('date','DESC')->get();
        if(count($data)==0){
            return $this->response->noContent();
        }
        return $this->response->array(['data'=>$data,'errCode'=>200]);
    }
    public function select(Request $request){
        $user =  Auth::guard('preordain')->user();
        $date = $request->date;
        $time = $request->time;
        $id = PreordainOpen::max('id');
        //数据库中有数据
        if ($id){
            $old_item = PreordainList::where('order_id',$id)->where('college',$user->name)->first();
            //已经预约过了
            if($old_item){
                return $this->response->error('您已经预约过了！',403);
            }
            $item = PreordainList::where('date',$date)->where('time',$time)->where('order_id',$id)->first();
            //找到该时间段
            if($item){
                $item->name = $user->name();
                $item->save();
                return $this->response->array(['data'=>[],'errCode'=>'200',])->setStatusCode(200);
            }
        }
        //数据库中没有该数据
        return $this->response->error('未找到该时间段！', 422);
    }
    public function updateSelect(Request $request){
        $user =  Auth::guard('preordain')->user();
        $id = PreordainOpen::max('id');
        //数据库中有数据
        if ($id){
            //找到以前的预约时间，将 college置空
            $old_item = PreordainList::where('order_id',$id)->where('college',$user->name)->first();
            if($old_item){
                $old_item->college = null;
                $old_item->save();
            }
            $date = $request->date;
            $time = $request->time;
            $new_item = PreordainList::where('date',$date)->where('time',$time)->where('order_id',$id)->first();
            //找到新的预约时间，将college设置为用户的name
            if($new_item){
                $new_item->name = $user->name();
                $new_item->save();
                return $this->response->array(['data'=>[],'errCode'=>'200',])->setStatusCode(200);
            }
        }
        return $this->response->error('未找到该时间段！', 422);
    }
    public function lastTime(){
        $id = PreordainOpen::max('id');
        if(!$id){
            return $this->response->noContent();
        }
        $data = PreordainOpen::find($id);
        return $this->response->array(['data'=>$data,'errCode'=>200]);

    }
    public function setTime(Request $request){
        $open = PreordainOpen::create($request->except('options'));
        foreach ($request->options as $option){
            foreach ($option['children'] as $children){
                $list = new PreordainList();
                $list->date = $option['value'];
                $list->time = $children['value'];
                $list->order_id = $open->id;
                $list->save();
            }
        }
        return $this->response->array(['data'=>[],'errCode'=>'200'])->setStatusCode(200);
    }
    public function updateTime(Request $request){
       $id = PreordainOpen::max('id');
       $item = PreordainOpen::find($id);
       $item->open_at = $request->open_at;
       $item->close_at = $request->close_at;
       $item->save();
       return $this->response->array(['data'=>[],'errCode'=>'200'])->setStatusCode(200);
    }
    protected function respondWithToken($token)
    {
        return $this->response->array(['data'=>[
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('preordain')->factory()->getTTL() * 60
        ],'errCode'=>200]);
    }
}

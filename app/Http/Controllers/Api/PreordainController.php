<?php

namespace App\Http\Controllers\Api;

use App\Models\PreordainList;
use App\Models\PreordainOpen;
use App\Models\PreordainUser;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Hash;
use Excel;

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
        $id = $request->id;
        $item = PreordainList::find($id);
        //找到该时间段
        if($item){
            if($item->college){
                return $this->response->error('该时间段已经被预约了！', 422);
            }
            $item->college = $user->name;
            $item->save();
            return $this->response->array(['data'=>[],'errCode'=>'200',])->setStatusCode(200);
        }else{
            //数据库中没有该数据
            return $this->response->error('未找到该时间段！', 422);
        }

    }
    public function deleteSelect($id){
        $user = Auth::user();
        $item = PreordainList::find($id);
        if($item){
            if($item->college==$user->name){
                $item->college = null;
                $item->save();
                return $this->response->array(['data'=>[],'errCode'=>'200',])->setStatusCode(200);
            }else{
                return $this->response->error('您未预约该时间段！', 422);
            }
        }else{
            return $this->response->error('未找到该时间段！', 422);
        }
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
    public function export($id){
        $preordainList = PreordainList::where('order_id',$id)->get();
        Excel::create('excel',function($excel) use($preordainList){
            $excel->sheet('预约信息', function($sheet) use ($preordainList){
               foreach ($preordainList as $item){
                   $sheet->appendRow([$item->date,$item->time,$item->college]);
               }
            });
        })->export('xls');
    }
}

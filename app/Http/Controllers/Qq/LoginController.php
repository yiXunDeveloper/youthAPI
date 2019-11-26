<?php

namespace App\Http\Controllers\Qq;

use App\Http\Requests\Qq\QqappAuthorizationRequest;
use App\Http\Requests\Qq\QqBasicInfoRequest;
use App\Http\Requests\QqBasicInfoTransformer;
use App\Models\QqUser;

use App\Transformers\QqUserTransformer;
use App\User;
use Auth;
use App\Models\QqUserBasic;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function qqappStore(QqappAuthorizationRequest $request)
    {
        //基础信息获取
        $appid = env('QQ_APP_ID', '1109907963');
        $screat = env('QQ_APP_SECRET', 'HMvNCSI92kAlxjGq');
        $js_code = $request->code;
        $grant_type = 'authorization_code';
        //获取code

        $data = $this->getSession($appid, $screat, $js_code, $grant_type); //获取失败时返回code为空
        if ($data['errcode']!=0) {
            return $this->response->array([
                'code' => $data['errcode'],
                'message' => $data['errmsg'],
            ]);
        }
        //找到 openid 对应的用户
        $user = QqUser::where('qqapp_openid', $data['openid'])->first();
        if($user){
            $usersBasic = QqUserBasic::where('user_id',$user->id)->first();
        }else{
            $usersBasic = null;
        }

        if (!$user) {
            $user = QqUser::create([
                'qqapp_openid' => $data['openid'],
                'qqapp_session_key' => $data['session_key'],
            ]);
            $usersBasic = QqUserBasic::create([
                'user_id' => $user->id
            ]); //保持同步
        }

        /**
         * 登录同时更新用户昵称与头像URL
         * 两表id一致，且都留有'nickName', 'avatarUrl'以防出错
         */
        
//        $userInfo = $request->only(['nickName', 'avatarUrl']);
//        $userBasicInfo = $request->only(['nickName', 'gender', 'avatarUrl', 'language', 'city', 'province', 'country']);
        //两表同步
//        $users = $user->update($userInfo);
//        $usersBasic =$usersBasic->update($userBasicInfo);

        if ($user && $usersBasic) {
            $token = Auth::guard('qq')->fromUser($user);
            return $this->respondWithToken($token)->setStatusCode(200);
        } else {
            return $this->respond(-1, '更新用户失败请稍后重试');
        }
    }
    public function update()
    {
        $token = Auth::guard('qq')->refresh();
        return $this->respondWithToken($token);
    }

    public function destroy()
    {
        Auth::guard('qq')->logout();
        return $this->respond(1,'删除成功');
    }

    public function me()
    {
        $data = new QqUserTransformer();
        $data = $data->transform($this->user());
        return $this->respond(-1,'请求成功',$data);
    }
    public function meUpdate(QqBasicInfoRequest $request)
    {
        $info = $request->only(['nickName', 'avatarUrl']);
        $basicInfo = $request->only(['nickName', 'gender', 'avatarUrl', 'language', 'city', 'province', 'country', 'name', 'school', 'offical', 'des', 'tags', 'level']);
        $user = $this->user();
        $userBasic = QqUserBasic::where('user_id',$user->id);
        $user = $user->update($info);
        $userBasic = $userBasic->update($basicInfo);
        if ($user && $userBasic) { //两者同时更新
            $data = new QqUserTransformer();
            $data = $data ->transform($this->user());
            return $this->respond(1,'更新成功',$data);
        } else {
            return $this->respond(-1, '更新失败请稍后重试');
        }
    }

    protected function respondWithToken($token)
    {
        $data = array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('qq')->factory()->getTTL() * 60,
        ]);
        return $this->response->array([
            'code' => '1',
            'data' => $data,
            'message' => '请求成功'
        ]);
    }

    protected function respond($code,$message,$data=null)
    {
        return $this->response->array([
            'code'=>$code,
            'data'=>$data,
            'message'=>$message
        ]);
    }

    protected function getSession($appid, $screat, $js_code, $grant_type)
    {
        $session_url = 'https://api.q.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $screat . '&js_code=' . $js_code . '&grant_type=' . $grant_type;
        //获取session
        $client = new Client();
        $res = $client->request('GET', $session_url);
        if ($res->getStatusCode() != 200) {
            return $this->response->error('源服务器错误', 500);
        }
        $body = $res->getbody();
        $contents = $body->getContents();
        $arr = json_decode($contents, true);
        return $arr;
    }
    
}

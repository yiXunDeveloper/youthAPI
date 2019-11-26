<?php

namespace App\Http\Controllers\Qq;

use App\Transformers\FanedTransformer;
use App\Transformers\FansTransformer;
use Illuminate\Http\Request;
use App\Models\QqFans;
use App\Http\Requests\Qq\FansRequest;

class FansController extends Controller
{
    
    public function fan($user_id){
        /**
         * user_id 为当前被关注者id   fan_id为当前操作者的id
         */
        //传过来的参数为当前文章作者id
        //找到操作对应的用户
        $operating_user = $this->user()->id;
        //检查fans表中有无信息
        // $user_id = $request->user_id;
        $data = QqFans::where('user_id', $user_id)
            ->where('fans_id', $operating_user)
            ->first();
        if ($data) {
            $data = $data->delete();
            if ($data) {
                return $this->respond(1, '取消关注成功');
            }
        } else {
            $data['user_id'] = $user_id;
            $data['fans_id'] = $this->user()->id;
            $data = QqFans::create($data);
            return $this->respond(1, '关注成功');
        }
    }

    public function fanedList()
    {
         $fans = QqFans::where('fans_id',$this->user()->id)->orderBy('created_at','DESC')->paginate(10);
        return $this->response->paginator($fans, new FanedTransformer());
    }

    public function fansList()
    {
        $fans = QqFans::where('user_id',$this->user()->id)->orderBy('created_at','DESC')->paginate(10);
        return $this->response->paginator($fans, new FansTransformer());
    }

    protected function respond($code, $message, $data = null)
    {
        return $this->response->array([
            'code' => $code,
            'data' => $data,
            'message' => $message
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\OaSigninDuty;
use App\Models\OaSigninRecord;
use App\Models\OaYouthUser;
use Illuminate\Http\Request;

class OAController extends Controller
{
    //
    public function getSignInLists(){
        $lists = OaSigninRecord::whereDate('created_at',date('Y-m-d'))->orderBy('updated_at','DESC')->get();
        foreach ($lists as $list){
            $list->user;
        }
        return $this->response->array(['data'=>$lists->toArray()])->setStatusCode(200);
    }
    public function updateSignRecord(Request $request){
        $sdut_id = $request->sdut_id;
        $user = OaYouthUser::where('sdut_id',$sdut_id)->first();
        if($user){
            $record = OaSigninRecord::where('sdut_id',$sdut_id)->whereDate('created_at',date('Y-m-d'))->where('status',0)->orderBy('created_at','DESC')->first();
            if($record){
                //需要判断用户角色
                if(!$user->duty){
                    $status = 4;//无效值班
                }else{
                    $arr = explode('|',$user->duty->duty_at);
                    $timer = 70;
                    $is_today = false;
                    $duty_area = 0;
                    $current_now = time();
                    $duration = ceil(($current_now - strtotime($record->created_at))/60);
                    foreach ($arr as $item){
                        if(substr($item,0,1) == date('w')){
                            $is_today = true;
                            $duty_area = substr($item,2,1);
                            break;
                        }
                    }
                    if($is_today){
                        switch ($duty_area){
                            case '1': $start_at = strtotime('08:00');$end_at = strtotime('09:50');break;
                            case '2': $start_at = strtotime('10:10');$end_at= strtotime('12:00');break;
                            case '3': $start_at = strtotime('14:00');$end_at = strtotime('15:50');break;
                            case '4': $start_at = strtotime('16:00');$end_at = strtotime('17:50');break;
                            case '5': $start_at = strtotime('21:00');$end_at = strtotime('23:00');break;
                            default:
                                 $start_at = strtotime('00:00');$end_at = strtotime('00:00');
                        }
                        if (strtotime($record->created_at) < $start_at && time() < $end_at && time() > $start_at) {
                            // 签到时间比规定时间早，签退时间比规定时间早
                            $duration = ceil((time() - $start_at) / 60);
                        } elseif (strtotime($record->created_at) < $start_at && time() >= $end_at) {
                            // 签到时间比规定时间早，签退时间比规定时间晚
                            $duration = ceil(($end_at - $start_at) / 60);
                        } elseif (strtotime($record->created_at) >= $start_at && time() >= $end_at && strtotime($record->created_at) < $end_at) {
                            // 签到时间比规定时间晚，签退时间比规定时间晚
                            $duration = ceil(($end_at - strtotime($record->created_at)) / 60);
                        } elseif (strtotime($record->created_at) >= $start_at && time() < $end_at) {
                            // 签到时间比规定时间晚，签退时间比规定时间早
                            $duration = ceil((time() - strtotime($record->created_at)) / 60);
                        }

                        if (strtotime($record->created_at) >= $end_at || $current_now < $start_at) {
                            //多余值班
                            $status = $duration >= $timer ? 2 : 4;
                        } else {
                            //不多余
                            $status = $duration >= $timer ? 1 : 3;
                        }
                    }else{
                        //不是今天值班
                        $status = $duration>=$timer ? 2 :4 ;
                    }
                }
                $record->status = $status;
                $record->duration = $duration;
                $record->save();
            }else{
                $id = OaSigninRecord::create([
                    'sdut_id'=>$sdut_id,
                ])->id;
                $record = OaSigninRecord::find($id);
            }
            $record->user;
            return $this->response->array(['data'=>$record])->setStatusCode(200);
        }else{
            return $this->response->error('用户不存在',404);
        }
    }
}

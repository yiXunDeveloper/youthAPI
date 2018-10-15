<?php

namespace App\Http\Controllers\Api;

use App\Models\OaEquipment;
use App\Models\OaEquipmentRecord;
use App\Models\OaSchedule;
use App\Models\OaSigninDuty;
use App\Models\OaSigninRecord;
use App\Models\OaYouthUser;
use Illuminate\Http\Request;
use Excel;

class OAController extends Controller
{
    //获取所有用户信息
    public function getUsers(){
        $users = OaYouthUser::orderBy('id','DESC')->get();
        return $this->response->array(['data'=>$users->toArray()]);
    }



    //获取当日签到记录
    public function getSignInLists(){
        $lists = OaSigninRecord::whereDate('created_at',date('Y-m-d'))->orderBy('updated_at','DESC')->get();
        foreach ($lists as $list){
            $list->user;
        }
        return $this->response->array(['data'=>count($lists) > 0 ? $lists->toArray() : $lists])->setStatusCode(200);
    }
    //   签到/签退
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
                            case '5': $start_at = strtotime('19:00');$end_at = strtotime('21:00');break;
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
                    $record->duration = $duration;
                }
                $record->status = $status;
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
    public function signRecordExport(Request $request){
        $validator = app('validator')->make($request->all(),[
            'start'=>'required|date',
            'end'=>'required|date|after:start'
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('Could not create new user.', $validator->errors());
        }
        $start_at = $request->start;
        $end_at = $request->end;
        $start_time = strtotime($start_at);
        $end_time = strtotime($end_at);
        //查询所选时间段值班记录
        $records = OaSigninRecord::whereBetween('created_at',[$start_at,$end_at])->get();
        $data = array();

        foreach ($records as $record){
            $duty = $record->duty;
            if ($duty){
                if(!array_key_exists($record->sdut_id,$data)){

                    preg_match_all('/(\d):\d/', $duty->duty_at, $dutys);
                    $n = 0;
                    for ($i=$start_time;$i<$end_time;$i+=86400){
                        if (date('w', $i) == $dutys[1][0] || date('w', $i) == $dutys[1][1]) {
                            $n++;
                        }
                    }
                    $data[$record->sdut_id]['name'] = $record->user->name;
                    $data[$record->sdut_id]['sdut_id'] = $record->sdut_id;
                    $data[$record->sdut_id]['department'] = $record->user->department;
                    $data[$record->sdut_id]['origin'] = $n; //本应签到次数，未除去节假日
                    $data[$record->sdut_id]['unsignout'] = 0;    //未签退次数
                    $data[$record->sdut_id]['normal'] = 0;       //正常签到次数
                    $data[$record->sdut_id]['normal_time'] = 0;    //正常签到时长
                    $data[$record->sdut_id]['surplus'] = 0;        //额外签到次数
                    $data[$record->sdut_id]['surplus_time'] = 0;   //额外签到时长
                    $data[$record->sdut_id]['early'] = 0;         //早退次数
                }//如果未初始化
                switch ($record->status) {
                    case 0:
                        $data[$record->sdut_id]['unsignout'] += 1;
                        break;
                    case 1:
                        $data[$record->sdut_id]['normal'] += 1;
                        $data[$record->sdut_id]['normal_time'] += intval($record->duration);
                        break;
                    case 2:
                        $data[$record->sdut_id]['surplus'] += 1;
                        $data[$record->sdut_id]['surplus_time'] += intval($record->duration);
                        break;
                    case 3:
                        $data[$record->sdut_id]['early'] += 1;
                        break;
                }
            }  //如果有值班任务
        }//endforeach
        Excel::create(date('Y-m-d H:i:s').'导出签到数据',function($excel) use($data){
            $excel->sheet('值班记录', function($sheet) use ($data){
                $sheet->fromArray($data);
            });
        })->export('xls');
    }

    public function getScheduleLists(){
        $last = date('Y-m-d H:i:s',strtotime("-1 month"));
        $lists = OaSchedule::whereTime('created_at','>',$last)->orderBy('updated_at','DESC')->get();
        foreach ($lists as $list){
            $list->sponsor_user;
        }
        return $this->response->array(['data'=>count($lists) > 0 ? $lists->toArray() : $lists])->setStatusCode(200);
    }
    public function getSchedule($id){
        $schedule = OaSchedule::find($id);
        return $this->response->array(['data'=>$schedule])->setStatusCode(200);
    }
    public function scheduleStore(Request $request){
        $this->validate($request,[
            'event_name' => 'required',
            'event_place' => 'required',
            'event_date' => 'required|date',
            'sponsor' => 'required|exists:oa_youth_users,sdut_id'
        ]);
        $schedule = OaSchedule::create($request->all());

        return $this->response->array(['data'=>$schedule])->setStatusCode(201);
    }
    public function scheduleUpdate(Request $request,$id){
        $this->validate($request,[
            'user'=>'required|exists:oa_youth_users,sdut_id'
        ]);
        $schedule = OaSchedule::find($id);
        if (!$schedule){
            return $this->response->errorNotFound('计划表未找到');
        }
        $schedule->event_status = 1;
        $schedule->save();
        return $this->response->array(['data'=>$schedule])->setStatusCode(200);
    }
    public function scheduleDelete($id){
        $schedule = OaSchedule::find($id);
        if (!$schedule){
            return $this->response->errorNotFound('计划表未找到');
        }
        $schedule->delete();
        return $this->response->noContent();
    }


    public function equipmentLists(){
        //查所有
        $equipments = OaEquipment::all();
        return $this->response->array(['data'=>$equipments]);
    }
    public function equipment($id){
        $equipment = OaEquipment::find($id);
        if(!$equipment){
            return $this->response->errorNotFound('设备未找到');
        }
        return $this->response->array(['data'=>$equipment]);
    }
    public function equipmentStore(Request $request){
        $this->validate($request,[
            'device_name' => 'required|unique:oa_equipment,device_name',
            'device_type' => 'required',
        ]);
        $equipment = OaEquipment::create($request->all());
        return $this->response->array(['data'=>$equipment]);
    }
    public function euqipmentDelete($id){
        //有token
        OaEquipment::find($id)->delete();
        return $this->response->noContent();
    }

    public function equipmentRecordLists(){
        //查一个月
        $last = date('Y-m-d H:i:s',strtotime("-1 month"));
        $lists = OaEquipmentRecord::whereTime('created_at','>',$last)->orderBy('updated_at','DESC')->get();
        foreach ($lists as $k =>$v){
            $v->device;
            $v->memo_user_name;
            if (is_numeric($v->lend_user)){
                //如果借用人事网站内部人员  通过模型关联获取信息
                $v->lend_user_name;
            }else{
                //否则，获取其lend_user
                $user = new OaYouthUser();
                $user->name = $v->lend_user;
                $lists[$k]->lend_user_name = $user;
            }
            if ($v->rememo_user){
                $v->rememo_user_name;
            }
        }
        return $this->response->array(['data'=>$lists]);
    }
    public function equipmentRecordStore(Request $request){
        $this->validate($request,[
            'device'=>'required|exists:oa_equipments,id',
            'activity'=>'required',
            'lend_at' => 'date',
            'lend_user' => 'required',        //站内学号，站外名称
            'memo_user' => 'required|exists:oa_youth_users,sdut_id'
        ]);
        if(OaEquipment::find($request->device)->status == 1){
            //设备已经被借用
            return $this->response->error('设备已被借用，不能重复借用',403);
        }
        if($request->lend_user == $request->user){
            return $this->response->error('借用人和借出备忘人不能为同一人！',500);
        }
        $sdut_id = $request->lend_user;
        if (strlen((int)$sdut_id) == strlen($sdut_id)){
            //全数字
            $user = OaYouthUser::where('sdut_id',$sdut_id)->first();
            if(!$user){
                return $this->response->errorNotFound('用户未找到');
            }
        }else if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$sdut_id)){
            //不是全是字符串
            return $this->response->error('借用人数据不合法',500);
        }
        $record = OaEquipmentRecord::create([
            'device_id' => $request->device,
            'activity' => $request->activity,
            'lend_at' => $request->lend_at,
            'lend_user' => $request->lend_user,
            'memo_user' => $request->memo_user,
        ]);
        $record->device;
        if (is_numeric($record->lend_user)){
            $record->lend_user_name;
        }else{
            $user = new OaYouthUser();
            $user->name = $record->lent_user;
            $record->lend_user_name = $user;
        }
        $record->memo_user_name;
        if ($record->rememo_user){
            $record->rememo_user_name;
        }
        return $this->response->array(['data'=>$record])->setStatusCode(201);
    }
    public function equipmentRecordUpdate(Request $request,$id){
        $this->validate($request,[
            'rememo_user' => 'required|exists:oa_youth_users,sdut_id',
        ]);
        $equipment_record = OaEquipmentRecord::find($id);
        if ($equipment_record){
            if ($equipment_record->sdut_id == $request->rememo_user){
                return $this->response->error('借用人和归还备忘人不能为同一人！',500);
            }
            $equipment_record->return_at = date('Y-m-d H:i:s');
            $equipment_record->rememo_user = $request->rememo_user;
            $equipment_record->save();
            return $this->response->noContent();
        }else{
            return $this->response->errorNotFound('未找到该记录');
        }
    }
    public function equipmentDelete($id){
        //验证token
        OaEquipmentRecord::find($id)->delete();
        return $this->response->noContent();
    }

    
}

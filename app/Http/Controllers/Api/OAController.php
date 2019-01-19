<?php

namespace App\Http\Controllers\Api;

use App\Models\OaEquipment;
use App\Models\OaEquipmentRecord;
use App\Models\OaPhonebook;
use App\Models\OaSchedule;
use App\Models\OaSigninDuty;
use App\Models\OaSigninRecord;
use App\Models\OaUser;
use App\Models\OaWorkload;
use App\Models\OaYouthUser;
use App\Models\ServiceHygiene;
use Auth;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OAController extends Controller
{
    //登录
    public function login(Request $request){
        $validator = app('validator')->make($request->all(),[
            'username'=>'required',
            'password'=>'required',
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('参数错误！');
        }
        $credentials['username'] = $request->username;
        $credentials['password'] = $request->password;
        //找到该用户
        if ($user = OaUser::where('username',$credentials['username'])->first())
        {
            //账号密码匹配
            if (Hash::check($credentials['password'],$user->password))
            {
                $token = Auth::guard('oa')->fromUser($user);
                return $this->response->array(['data'=>[
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => Auth::guard('oa')->factory()->getTTL() * 60
                ]])->setStatusCode(200);
            }else {
                //账号密码不匹配
                return $this->response->errorUnauthorized('密码错误');
            }
        }else{
            //未找到该用户
            return $this->response->errorUnauthorized('未找到该用户');
        }
    }


   //导入用户信息
    public function importUserInfo(Request $request) {
        $user = Auth::guard('oa')->user();
        if (!$user->can('manage_user') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        //获取上传的文件
        $excel = $request->file('excel');
        $file = $excel->store('excel');
        //Excel加载文件
        Excel::load(public_path('app/').$file,function ($reader){
            $reader = $reader->getSheet(0);
            $res = $reader->toArray();
            //如果没有数据或者表格的列数不等于8，报错
            if(sizeof($res) <= 1 || sizeof($res[0]) != 8) {
                return $this->response->error('文件数据不合法',422);
            }
            $user = OaUser::where('username','youthol')->first();
            $ps = $user ? $user->password : bcrypt('youth123');
            OaYouthUser::truncate();
            OaUser::truncate();
            OaSigninDuty::truncate();
            DB::table('model_has_roles')->truncate();
            $user = new OaUser();
            $user->username = 'youthol';
            $user->password = $ps;
            $user->sdut_id = '00000000000';
            $user->save();
            if (!$user->hasRole('Root')) {
                $user->assignRole('Root');
            }
            unset($res[0]);
            foreach ($res as $key => $value) {
                $birthday = str_replace('\/','-',$value[5]);
                OaYouthUser::create([
                    'sdut_id' => $value[0],
                    'name' => $value[1],
                    'department' => $value[2],
                    'grade' => $value[3],
                    'phone' => $value[4] ? $value[4] : null,
                    'birthday' => $birthday ? $birthday : null,
                ]);
                if ($value[6]) {
                    if (preg_match("/[0-6]:[1-5]|[0-6]:[1-5]/",$value[6]) == 0 && preg_match("/[0-6]:[1-5]/",$value[6])==0) {
                        return $this->response->error("{$value[0]}{$value[1]}的duty:{$value[6]}数据不合法",422);
                    }
                    $user_duty = new OaSigninDuty();
                    $user_duty->sdut_id = $value[0];
                    $user_duty->duty_at = $value[6];
                    $user_duty->save();
                }
                $user = OaUser::create([
                    'username' => $value[0],
                    'password' => bcrypt($value[0]),
                    'sdut_id' => $value[0],
                ]);
                $roles = explode('|',$value[7]);
                $roles = Role::whereIn('display_name',$roles)->get(['name']);
                foreach ($roles as $role) {
                    $user->assignRole($role->name);
                }
            }
        });
        return $this->response->array(['data'=>'导入成功'])->setStatusCode(200);
    }
    //导出用户
    public function exportUser() {
        $user = Auth::guard('oa')->user();
        if (!$user->can('manage_user') && !$user->can('manage_administrator')) {
            return $this->response->error('您没有该权限！', 403);
        }
        $users = OaYouthUser::all();
        $data = array();
        foreach ($users as $user) {

            $data[$user->sdut_id]['sdut_id'] = $user->sdut_id;
            $data[$user->sdut_id]['name'] = $user->name;
            $data[$user->sdut_id]['department'] = $user->department;
            $data[$user->sdut_id]['grade'] = $user->grade;
            $data[$user->sdut_id]['phone'] = $user->phone;
            $data[$user->sdut_id]['birthday'] = $user->birthday;
            $data[$user->sdut_id]['duty_at'] = $user->duty ? $user->duty->duty_at : "";
            $roles = $user->user->roles;
            $name = array();
            foreach ($roles as $role) {
                array_push($name,$role->display_name);
            }
            $data[$user->sdut_id]['role'] = implode("|",$name);
        }
        Excel::create(date('Y-m-d H:i:s').'导出用户数据',function($excel) use($data){
            $excel->sheet('用户数据', function($sheet) use ($data){
                $sheet->fromArray($data);
            });
        })->export('xls');
    }
    //卫生成绩导入
    public function importHygiene(Request $request) {

        $excel = $request->file('dormitory');
        $file = $excel->store('excel');
        //如果没有数据或者表格的列数不等于8，报错
        Excel::load(public_path('app/').$file,function ($reader){
            $reader = $reader->getSheet(0);
            $res = $reader->toArray();

            if(sizeof($res) <= 1 || sizeof($res[0]) != 7) {
                throw new \Dingo\Api\Exception\StoreResourceFailedException('文件格式错误！', ['dormitory'=>'Excel文件为空或列数不等于7']);
            }
            //删除前两行无用信息
            unset($res[0]);
            unset($res[1]);
            foreach ($res as $value) {
                ServiceHygiene::create([
                    'date' => $value[0],
                    'week' => $value[1],
                    'dormitory' => $value[2],
                    'room' =>$value[3],
                    'score' => $value[4],
                    'academy' => $value[5],
                    'member' => $value[6]
                ]);
            }
        });
        return $this->response->array(['data'=>'导入成功'])->setStatusCode(200);

    }


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
                    //通过‘|’将用户的多个值班任务分开
                    $arr = explode('|',$user->duty->duty_at);
                    $timer = 70; //值班时间70分钟为合格
                    $is_today = false; //今天是否有值班任务
                    $duty_area = 0;   //当日签到任务（1-5）
                    $current_now = time();  //当前时间
                    $duration = ceil(($current_now - strtotime($record->created_at))/60);  //此次签退距上次签到的时间段
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
                        $created_at = strtotime($record->created_at);

                        if ($created_at < $start_at && $current_now < $end_at && $current_now > $start_at) {
                            // 签到时间比规定签到时间早，签退时间比规定签退时间早但是比规定签到时间晚
                            $duration = ceil(($current_now - $start_at) / 60);
                        } elseif ($created_at <= $start_at && $current_now >= $end_at) {
                            // 签到时间比规定签到时间早，签退时间比规定签退时间晚
                            $duration = ceil(($end_at - $start_at) / 60);
                        } elseif ($created_at >= $start_at && $current_now >= $end_at && strtotime($record->created_at) < $end_at) {
                            // 签到时间比规定签到时间晚但比规定签退时间早，签退时间比规定签退时间晚
                            $duration = ceil(($end_at - strtotime($record->created_at)) / 60);
                        } elseif ($created_at >= $start_at && $current_now < $end_at) {
                            // 签到时间比规定签到时间晚，签退时间比规定时间早
                            $duration = ceil(($current_now - strtotime($record->created_at)) / 60);
                        }

                        if ($created_at >= $end_at || $current_now < $start_at) {
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

    //导出签到记录
    public function ExportSignRecord(Request $request){
        $validator = app('validator')->make($request->all(),[
            'start'=>'required|date',
            'end'=>'required|date|after:start'
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据格式错误！', $validator->errors());
        }
        $start_at = $request->start;
        $end_at = $request->end;
        $start_time = strtotime($start_at);
        $end_time = strtotime($end_at);
        //查询所选时间段值班记录
        $records = OaSigninRecord::whereBetween('created_at',[$start_at,$end_at])->get();
        $data = array();//全部记录

        foreach ($records as $record){
            $duty = $record->duty;
            //如果用户有值班任务，进入下一步，否则不进行统计
            if ($duty){
                //如果已获取签到记录中没有该用户，需要初始化（计算时间段应签到次数，初始化项其他为0）
                if(!array_key_exists($record->sdut_id,$data)){

                    preg_match_all('/(\d):\d/', $duty->duty_at, $dutys);//匹配用户值班日期和节数

                    $n = 0;          //选定时间段应签到次数
                    if (count($dutys[1]) == 2) {
                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (date('w', $i) == $dutys[1][0] || date('w', $i) == $dutys[1][1]) {
                                $n++;
                            }
                        }
                    }else if (count($dutys[1]) == 1) {
                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (date('w', $i) == $duty[1][0]) {
                                $n++;
                            }
                        }
                    } else {
                        return $record->sdut_id . "的duty错误，duty：" . $duty->duty_at;
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
    //获得当月计划表
    public function getSchedules(){
        $last = date('Y-m-d H:i:s',strtotime("-1 month"));
        $lists = OaSchedule::whereDate('created_at','>',$last)->orderBy('updated_at','DESC')->get();
        foreach ($lists as $list){
            $list->sponsor_user;
        }
        return $this->response->array(['data'=>count($lists) > 0 ? $lists->toArray() : $lists])->setStatusCode(200);
    }
    //通过id获取计划表
    public function getSchedule(OaSchedule $schedule){
        return $this->response->array(['data'=>$schedule])->setStatusCode(200);
    }
    //增加计划表
    public function addSchedule(Request $request){
        $validator = app('validator')->make($request->all(),[
            'event_name' => 'required',
            'event_place' => 'required',
            'event_date' => 'required|date',
            'sponsor' => 'required|exists:oa_youth_users,sdut_id'
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据格式错误', $validator->errors());
        }
        $schedule = OaSchedule::create($request->all());

        return $this->response->array(['data'=>$schedule])->setStatusCode(201);
    }
    //修改计划表
    public function updateSchedule(Request $request,OaSchedule $schedule){
        $validator = app('validator')->make($request->all(),[
            'user'=>'required|exists:oa_youth_users,sdut_id'
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据格式错误', $validator->errors());
        }
        $schedule->event_status = 1;
        $schedule->save();
        return $this->response->array(['data'=>$schedule])->setStatusCode(200);
    }
    //删除计划表
    public function deleteSchedule(OaSchedule $schedule){
        $user = Auth::guard('oa')->user();
        if($user->can('manage_activity')){

            $schedule->delete();
        }else{
            return $this->response->error('对不起，您无权限进行该操作！',403);
        }
        return $this->response->noContent();
    }

    //查询所有设备
    public function getEquipments(){
        //查所有
        $equipments = OaEquipment::all();
        return $this->response->array(['data'=>$equipments]);
    }
    //通过id获得设备
    public function getEquipmentById(OaEquipment $equipment){
        return $this->response->array(['data'=>$equipment]);
    }
    //增加设备
    public function addEquipment(Request $request){
        $validator = app('validator')->make($request->all(),[
            'device_name' => 'required|unique:oa_equipment,device_name',
            'device_type' => 'required',
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据格式错误', $validator->errors());
        }
        $equipment = OaEquipment::create($request->all());
        return $this->response->array(['data'=>$equipment]);
    }
    //删除设备
    public function deleteEquipment(OaEquipment $equipment){
        //有token
        $user = Auth::guard('oa')->user();
        if (!$user->can('manage_device')) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("您没有该权限！");
        }
        $equipment->delete();
        return $this->response->noContent();
    }
    //查询最近一个月的设备借用记录
    public function getEquipmentRecords(){
        //查一个月
        $last = date('Y-m-d',strtotime("-1 month"));
        $lists = OaEquipmentRecord::whereDate('created_at','>',$last)->orderBy('updated_at','DESC')->get();


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
//    增加设备借用记录
    public function addEquipmentRecord(Request $request){
        $validator = app('validator')->make($request->all(),[
            'device'=>'required|exists:oa_equipments,id',
            'activity'=>'required',
            'lend_at' => 'date',
            'lend_user' => 'required',        //站内学号，站外名称
            'memo_user' => 'required|exists:oa_youth_users,sdut_id'
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据格式错误', $validator->errors());
        }
        $equipment = OaEquipment::find($request->device);
        if($equipment->status == 1){
            //设备已经被借用
            return $this->response->error('设备已被借用，不能重复借用',403);
        }
        if($request->lend_user == $request->memo_user){
            return $this->response->error('借用人和借出备忘人不能为同一人！',422);
        }
        $sdut_id = $request->lend_user;
        if (strlen((int)$sdut_id) == strlen($sdut_id)){
            //全数字
            $user = OaYouthUser::where('sdut_id',$sdut_id)->first();
            if(!$user){
                return $this->response->errorNotFound('借用人未找到');
            }
        }else if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$sdut_id)){
            //全是中文汉字
            $memo = OaYouthUser::where('name',$sdut_id)->first();
            if($memo && $sdut_id == $memo->name){
                return $this->response->error('站内人员借用需输入学号！',422);
            }
        }else{
            return $this->response->error('借用人数据不合法',422);
        }
        $record = OaEquipmentRecord::create([
            'device_id' => $request->device,
            'activity' => $request->activity,
            'lend_at' => $request->lend_at,
            'lend_user' => $request->lend_user,
            'memo_user' => $request->memo_user,
        ]);
        $equipment->status = 1;  //状态设置为借用中
        $equipment->save();
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
    //归还设备
    public function updateEquipmentRecord(Request $request,OaEquipmentRecord $record){
        $validator = app('validator')->make($request->all(),[
            'rememo_user' => 'required|exists:oa_youth_users,sdut_id',
        ]);
        if ($validator->fails()){
            throw new \Dingo\Api\Exception\StoreResourceFailedException('归还备忘人错误', $validator->errors());
        }
        if ($record){
            if ($record->lend_user == $request->rememo_user){
                return $this->response->error('借用人和归还备忘人不能为同一人！',422);
            }
            $record->return_at = date('Y-m-d H:i:s');
            $record->rememo_user = $request->rememo_user;
            $record->save();
            //修改设备状态
            $equipment = OaEquipment::find($record->device);
            $equipment->status = 0; //设置为未借用
            $equipment->save();
            return $this->response->noContent();
        }else{
            return $this->response->errorNotFound('未找到该记录');
        }
    }
    //删除设备借还记录
    public function deleteEquipmentRecord(OaEquipmentRecord $record){
        //有token
        $user = Auth::guard('oa')->user();
        if (!$user->can('manage_device')) {

            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("您没有该权限！");
        }
        $record->delete();
        return $this->response->noContent();
    }

    //电话簿管理

    //获取全部电话簿
    public function getPhonebooks() {
        $phonebooks = OaPhonebook::all();
        return $this->response->array(['data'=>$phonebooks])->setStatusCode(200);
    }
//    添加电话簿
    public function addPhonebook(Request $request) {
        $user = Auth::guard('oa')->user();
        if (!$user->can('manage_phone_book')){
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("您没有该权限！");
        }
        $validator = app('validator')->make($request->all(),[
            'administrative_unit'=>'required',
            'office_location' => 'required',
            'office_person' => 'required',
            'telephone' => 'required',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据不完整', $validator->errors());
        }
        OaPhonebook::create($request->all());
        return $this->response->noContent();
    }
//    删除电话簿
    public function deletePhonebook(OaPhonebook $phonebook) {
        $user = Auth::guard('oa')->user();
        if (!$user->can('manage_phone_book')){
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("您没有该权限！");
        }
        $phonebook->delete();
        return $this->response->noContent();
    }

    //获取工作量
    public function getWorkloads() {

    }

    //增加工作量
    public function addWorkloads(Request $request){
        $user = Auth::guard('oa')->user();
        if (!$user->hasRole('Administrator')) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("只有管理员才能进行该操作！");
        }
        $validator = app('validator')->make($request->all(),[
            'sdut_id' => 'required|exists:oa_users,sdut_id',
            'description' => 'required',
            'score' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据错误！', $validator->errors());
        }
        OaWorkload::create([
            'sdut_id' => $request->sdut_id,
            'description' => $request->description,
            'score' => $request->score,
            'manager_id' => $user->sdut_id,
        ]);
        return $this->response->noContent();
    }

    //修改工作量
    public function updateWorkload(Request $request,OaWorkload $workload) {
        $user = Auth::guard('oa')->user();
        if (!$user->hasRole('Administrator')) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("只有管理员才能进行该操作！");
        }
        $validator = app('validator')->make($request->all(),[
            'description' => 'required',
            'score' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException('数据错误！', $validator->errors());
        }
        $workload->description = $request->description;
        $workload->score = $request->score;
        $workload->manager_id = $user->sdut_id;
        $workload->save();
        return $this->response->noContent();
    }

    //删除工作量
    public function deleteWorkload(OaWorkload $workload) {
        $user = Auth::guard('oa')->user();
        if (!$user->hasRole('Administrator')) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("只有管理员才能进行该操作！");
        }
        $workload->delete();
        return $this->response->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Dormitory;
use App\Models\ServiceExamMeta;
use App\Models\ServiceExamTime;
use App\Models\ServiceExamGkl;
use App\Models\ServiceHygiene;
use App\Models\ServiceNewStudent;
use App\Models\ServiceUser;
use Auth;
use Dingo\Api\Exception\StoreResourceFailedException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class FeatureController extends Controller
{
    protected $cookie_file;

    public function authorization(Request $request) {
        $validator = app('validator')->make($request->all(),[
            'code' => 'required'
        ]);
        if ($validator->fails()) {
            throw new StoreResourceFailedException("参数不正确",$validator->errors());
        }
        $driver = Socialite::driver('weixin');
        $response = $driver->getAccessTokenResponse($request->code);
        $user = ServiceUser::where('openid',$response['openid'])->first();
        if (!$user) {
            $user = ServiceUser::create([
                'openid' => $response['openid'],
            ]);
        }
        return $this->response->array(['data'=>$user,'meta'=>[
            'access_token' => Auth::guard('service')->fromUser($user),
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('service')->factory()->getTTL() * 60,
        ]])->setStatusCode(201);
    }

    public function newStudent(Request $request)
    {
        $num = $request->num;
        if(strlen($num)==11){
            $new_student = ServiceNewStudent::where('sdut_id',$num)->first();
        }else{
            $new_student = ServiceNewStudent::where('kaohao',$num)->first();
        }
       if($new_student)
       {
           $sheyou = ServiceNewStudent::where('dormitory',$new_student->dormitory)->where('room',$new_student->room)->where('bed','<>',$new_student->bed)->orderBy('bed','ASC')->get(['name','class','bed']);
           return $this->response->array([
               'name'=>$new_student->name,
               'sdut_id'=>$new_student->sdut_id,
               'college'=>$new_student->college,
               'major'=>$new_student->major,
               'class'=>$new_student->class,
               'school'=>$new_student->school,
               'dormitory'=>$new_student->dormitory,
               'room'=>$new_student->room,
               'bed'=>$new_student->bed,
               'roommate'=>$sheyou
           ])->setStatusCode(200);
       }else{
           return $this->response->errorNotFound('没有该学生信息');
       }
//       $url = 'http://211.64.28.125/sdut_q/query';
//       $this->get_cookie($url);
//       $data = $this->http_request_post($url,['ticketNumber'=>'18371202151375'],1);
//        return $data;
    }
    public function dormitory(){
        $dormitory = Dormitory::all();
        return $this->response->array(['data'=>$dormitory->toArray()])->setStatusCode(200);
    }
    //宿舍成绩
    public function hygiene()
    {
        $lroom = \request('dormitory');
        $croom = intval(\request('room'));
        $room = $lroom.$croom;
        $data = ServiceHygiene::where('room','=',$room)->orderBy('week','asc')->get();
        if(count($data)>0){
            return $this->response->array(['data'=>$data->toArray()])->setStatusCode(200);
        }else{
            return $this->response->errorNotFound("参数错误，未获取到房间为{$room}宿舍卫生信息");
        }
    }
    //考试时间
    public function exam(){
        $sdut_id = \request('sdut_id');
        $exam_times = ServiceExamTime::where('sdut_id',$sdut_id)->orderBy('date','ASC')->get();
        $data = array();
        foreach ($exam_times as $exam_time){
            $exam_meta = ServiceExamMeta::where('date',$exam_time->date)->where('classroom',$exam_time->classroom)->first();
            $gkl = ServiceExamGkl::where('course',$exam_time->course)->first();
//            $exam_time = $exam_time->toArray();
            $exam_time->meta = $exam_meta;
            $exam_time->gkl = count($gkl)?$gkl->gkl:null;
            array_push($data,$exam_time->toArray());
        }
        if(count($data)>0){
            return $this->response->array(['data'=>$data])->setStatusCode(200);
        }else{
            return $this->response->errorNotFound("对不起，未获取到学号为{$sdut_id}考试时间信息");
        }
    }

    public function index(){

    }
    public function elec(Request $request){
        $school = $request->school;
        $dormitory = $request->dormitory;
        $room = $request->room;
//        dd($request->all());
//        $school = 1;
//        $dormitory = '01#南';
//        $room = 101;
        $url_cookie='http://hqfw.sdut.edu.cn';
        $this->cookie_file = public_path('cookie\cookie.txt');//选择cookie储存路径
        $this->get_cookie($url_cookie);  //获取cookie
        $url1 = 'http://hqfw.sdut.edu.cn/login.aspx';  //带着cookie获取input参数
        $res1 = $this->http_request_post($url1,'',true);
        preg_match_all('#<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^<>]+)" />#', $res1, $value1);
        preg_match_all('#<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="([^<>]+)" />#', $res1, $value2);
        $post1=array(
            '__VIEWSTATE'=>$value1[1][0],
            '__EVENTVALIDATION'=>$value2[1][0],
            'ctl00$MainContent$txtName'=>'孙骞',
            'ctl00$MainContent$txtID'=>'15110201098',
            'ctl00$MainContent$btnTijiao'=>'登录'
        );
        $this->http_request_post($url1,$post1,true);  //带着cookie登录
        $url2 = 'http://hqfw.sdut.edu.cn/stu_elc.aspx';             //查询地址
        $res2 = $this->http_request_post($url2,'',true);  //带着cookie获取input参数
        preg_match_all('#<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^<>]+)" />#', $res2, $value3);
        preg_match_all('#<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="([^<>]+)" />#', $res2, $value4);
        preg_match_all('#<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="([^<>]+)" />#',$res2,$value5);
        if ($school == 1){
            //西校区
            $building='ctl00$MainContent$buildingwest';
            $campus='1';
        }else{
            $building='ctl00$MainContent$buildingeast';
            $campus='0';
            $post2=array(
                '__EVENTTARGET'=>'ctl00$MainContent$campus',//
                '__EVENTARGUMENT'=>'',
                '__LASTFOCUS'=>'',
                '__VIEWSTATE'=>$value3[1][0],
                '__VIEWSTATEGENERATOR'=>$value5[1][0],
                '__EVENTVALIDATION'=>$value4[1][0],
                'ctl00$MainContent$campus'=>$campus,
                'ctl00$MainContent$buildingwest'=>'01#南',
                'ctl00$MainContent$roomnumber'=>'101',
//            'ctl00$MainContent$Button1'=>'查询',
                'ctl00$MainContent$TextBox1'=>'请先登录，再选择楼栋和输入房间号查询!'
            );
            $res3 = $this->http_request_post($url2,$post2,true);  //带着cookie查询电费
            preg_match_all('#<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^<>]+)" />#', $res3, $value3);
            preg_match_all('#<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="([^<>]+)" />#', $res3, $value4);
            preg_match_all('#<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="([^<>]+)" />#',$res3,$value5);

        }

        $post2=array(
            'EVENTTARGET'=>'',//
            '__EVENTARGUMENT'=>'',
            '__LASTFOCUS'=>'',
            '__VIEWSTATE'=>$value3[1][0],
            '__VIEWSTATEGENERATOR'=>$value5[1][0],
            '__EVENTVALIDATION'=>$value4[1][0],
            'ctl00$MainContent$campus'=>$campus,
            $building=>$dormitory,
            'ctl00$MainContent$roomnumber'=>$room,
            'ctl00$MainContent$Button1'=>'查询',
            'ctl00$MainContent$TextBox1'=>'请先登录，再选择楼栋和输入房间号查询!'
        );
//        dd($post2);
        $res3 = $this->http_request_post($url2,$post2,true);  //带着cookie查询电费
        str_replace('/\r\n/','',$res3);
        preg_match_all('#您所查询的房间为：([^<>]+)。\r\n 在([^<>]+)时，所余电量为：([^<>]+)度。\r\n 根据您的用电规律，所余电量可用 ([^<>]+)天。\r\n 当前用电状态为：([^<>]+)。#', $res3, $value5);
        if (!empty($value5[0])){
            return $this->response->array(['data'=>['room'=>$value5[1][0],'time'=>$value5[2][0],'elec'=>$value5[3][0],'remain'=>$value5[4][0],'status'=>$value5[5][0]]]);
        }else{
            return $this->response->error('所查询房间不存在或服务器错误',404);
        }


    }
    public function test(){
        $param =  [
            'user'=>'16111101135',
            'passwd' => 'hu16111101135',
            'auth' => 0
        ];
        $client = new Client();
        $res = $client->request('POST','http://api.youthol.cn/getkb/allscore',[
            'form_params' => $param,
        ]);
        $result = json_decode($res->getBody());
        dd($result);
    }
    
















    function http_request_get($url){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT_MS,2000);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return $tmpInfo;    //返回json对象
    }
    function http_request_post($url,$data,$use_cookie=0){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT_MS,2000);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        if($use_cookie){
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }
    function get_cookie($url){
//        $this->cookie_file = storage_path('app\public\cookie.txt');
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
//        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl, CURLOPT_HTTPGET, 1);
//        curl_setopt($curl,  CURLOPT_COOKIEJAR, $this->cookie_file);
//
//        $result = curl_exec($curl);
//        curl_close($curl);
//        return $result;
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS,'');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\College;
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
use GuzzleHttp\Cookie\CookieJar;
use App\Libs\Base64;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use QL\QueryList;

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
        if ($user->sdut_id != null) {
            //用户已绑定
            $data = array([
                'sdut_id' => $user->sdut_id,
                'college'=>$user->college,
                'class' => $user->class,
                'dormitory' => $user->dormitory,
                'room' => $user->room,
                'password_jwc' => $user->password_jwc == null ? null : decrypt($user->password_jwc),
                'password_dt' => $user->password_dt == null ? null : decrypt($user->password_dt),
            ]);
            return $this->response->array(['data'=>$data,'meta'=>[
                'access_token' => Auth::guard('service')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => Auth::guard('service')->factory()->getTTL() * 60,
            ],'code' => 0])->setStatusCode(201);
        }else {
            return $this->response->array(['meta'=>[
                'access_token' => Auth::guard('service')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => Auth::guard('service')->factory()->getTTL() * 60,
            ],'code' => -1])->setStatusCode(201);
        }

    }

    public function index(){
        $user = Auth::guard('service')->user();
        $data = array([
            'sdut_id' => $user->sdut_id,
            'college'=>$user->college,
            'class' => $user->class,
            'dormitory' => $user->dormitory,
            'room' => $user->room,
            'password_jwc' => $user->password_jwc == null ? null : decrypt($user->password_jwc),
            'password_dt' => $user->password_dt == null ? null : decrypt($user->password_dt),
        ]);
        return $this->response->array(['data'=>$data])->setStatusCode(200);
    }
    public function updateUser(Request $request) {
        $user = Auth::guard('service')->user();
        $validator = app('validator')->make($request->all(),[
            'sdut_id' => 'sometimes|size:11',
            'college' => 'sometimes|exists:colleges,id',
            'dormitory' => 'sometimes|exists:dormitorys,id',
        ]);
        if ($validator->fails()) {
            throw new StoreResourceFailedException("信息错误！",$validator->errors());
        }
        //填了学号和教务处密码，验证是否正确
        if ($request->sdut_id != null && $request->password_jwc!=null) {
            $jar = $this->loginJWC($request->sdut_id,$request->password_jwc);
            if ($jar == null) {
                throw new StoreResourceFailedException("学号或教务处密码错误");
            }
        }

        $user->sdut_id = $request->sdut_id;
        $user->college_id = $request->college;
        $user->dormitory_id = $request->dormitory;
        $user->class = $request->class;
        $user->room = $request->room;
        $user->password_jwc = $request->password_jwc == null ? null : encrypt($request->password_jwc);
        $user->password_dt = $request->password_dt == null ? null : encrypt($request->passwprd_dt);
        $user->save();
        return $this->response->noContent();
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

    public function college() {
        $colleges = College::all();
        return $this->response->array(['data'=>$colleges->toArray()])->setStatusCode(200);
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

    public function elec(Request $request){
        $school = $request->school;
        $dormitory = $request->dormitory;
        $room = $request->room;
        $jar = new CookieJar();
        $client = new Client();

        $login_url = 'http://hqfw.sdut.edu.cn/login.aspx';  //获取登录参数
        $elec_url = 'http://hqfw.sdut.edu.cn/stu_elc.aspx';  //查询地址

        //获取登录参数
        $res = $client->request('GET',$login_url,['cookies'=>$jar]);
        $ql = QueryList::html($res->getBody());
        $viewstate =$ql->find('#__VIEWSTATE')->val();
        $event = $ql->find('#__EVENTVALIDATION')->val();
        $client->request('POST',$login_url,[
            'cookies'=>$jar,
            'form_params'=> [
                '__VIEWSTATE'=>$viewstate,
                '__EVENTVALIDATION'=>$event,
                'ctl00$MainContent$txtName'=>'孙骞',
                'ctl00$MainContent$txtID'=>'15110201098',
                'ctl00$MainContent$btnTijiao'=>'登录'
            ],
        ]);
        $res = $client->request('GET',$elec_url,['cookies'=>$jar]);
        $ql = QueryList::html($res->getBody());
        $viewstate =$ql->find('#__VIEWSTATE')->val();
        $event = $ql->find('#__EVENTVALIDATION')->val();
        if ($school == 1){
            //西校区
            $building='ctl00$MainContent$buildingwest';
            $campus='1';
        }else{
            $building='ctl00$MainContent$buildingeast';
            $campus='0';
            $post2=array(
                '__VIEWSTATE'=>$viewstate,
                '__EVENTVALIDATION'=>$event,
                'ctl00$MainContent$campus'=>$campus,
            );
            $res = $client->request('POST',$elec_url,[
                'cookies'=>$jar,
                'form_params'=>$post2
            ]);
            $ql = QueryList::html($res->getBody());
            $viewstate =$ql->find('#__VIEWSTATE')->val();
            $event = $ql->find('#__EVENTVALIDATION')->val();
        }

        $post2=array(
            '__VIEWSTATE'=>$viewstate,
            '__EVENTVALIDATION'=>$event,
            'ctl00$MainContent$campus'=>$campus,
            $building=>$dormitory,
            'ctl00$MainContent$roomnumber'=>$room,
            'ctl00$MainContent$Button1'=>'查询',
        );
        $res = $client->request('POST',$elec_url,[
            'cookies' => $jar,
            'form_params' => $post2,
            'http_errors' => false,
        ]);
        $res = $res->getBody();
        str_replace('/\r\n/','',$res);
        preg_match_all('#您所查询的房间为：([^<>]+)。\r\n 在([^<>]+)时，所余电量为：([^<>]+)度。\r\n 根据您的用电规律，所余电量可用 ([^<>]+)天。\r\n 当前用电状态为：([^<>]+)。#', $res, $value);
        if (isset($value[0])&&!empty($value[0])){
            return $this->response->array(['data'=>['room'=>$value[1][0],'time'=>$value[2][0],'elec'=>$value[3][0],'remain'=>$value[4][0],'status'=>$value[5][0]]]);
        }else{
            return $this->response->error('所查询房间不存在或服务器错误',404);
        }


    }
    public function test(Request $request){

//        $this->loginJWC($request->sdut_id,$request->password_jwc);
        return $this->loginEhall($request->sdut_id,$request->password_jwc);
    }
    
    protected function loginJWC($sdut_id,$password) {
        $jar = new CookieJar();
        $client = new Client();
        $res = $client->request('GET',"http://210.44.191.124/jwglxt/xtgl/login_slogin.html",[
            'cookies' => $jar,
        ]);
        $csrf = QueryList::html($res->getBody())->find('#csrftoken')->val();
        $rsakey = $client->request('GET','http://210.44.191.124/jwglxt/xtgl/login_getPublicKey.html',[
            'cookies' => $jar,
        ]);
        $rsainfo = json_decode($rsakey->getBody());
        $rsa = new RSA();
        $publicKey = array(
            'n' => new BigInteger(Base64::b64tohex($rsainfo->modulus), 16),
            'e' => new BigInteger(Base64::b64tohex($rsainfo->exponent), 16),
        );
        $rsa->loadKey($publicKey);
        $rsa->setEncryptionMode(2);
        $en_pwd = $rsa->encrypt($password);
        $en_pwd = bin2hex($en_pwd);
        $password = Base64::hex2b64($en_pwd);
//        return $password;
        $res = $client->request('POST',"http://210.44.191.124/jwglxt/xtgl/login_slogin.html",[
            'cookies' => $jar,
            'form_params' => [
                'csrftoken' => $csrf,
                'yhm' => $sdut_id,
                'mm' => $password,
                'mm' => $password,
            ],
            'http_errors'=>false,
        ]);
        if (preg_match("/修改密码/",$res->getBody()) == 1) {
            //证明登录成功，返回cookie
            return $jar;
        }else  {
            //未匹配成功，登录失败
            return null;
        }
    }

    protected function loginEhall($sdut_id,$password) {
        $jar = new CookieJar();
        $client = new Client(['cookies'=>$jar]);
        $login_url = "http://authserver.sdut.edu.cn/authserver/login?service=http%3A%2F%2Fehall.sdut.edu.cn%2Flogin%3Fservice%3Dhttp%3A%2F%2Fehall.sdut.edu.cn%2Fnew%2Fehall.html";
        $res = $client->request('GET',$login_url);
        if ($res->getStatusCode()!=200) {
            return $this->response->error('源服务器错误',500);
        }
        $ql = QueryList::html($res->getBody());
        $lt = $ql->find("input[name='lt']")->val();
        $dtlt = $ql->find("input[name='dllt']")->val();
        $execution = $ql->find("input[name='execution']")->val();
        $_evenId = $ql->find("input[name='_eventId']")->val();
        $rmShown = $ql->find("input[name='rmShown']")->val();
        $result = $client->request('POST',$login_url,[
            'form_params'=>[
                'username' => $sdut_id,
                'password' => $password,
                'lt' => $lt,
                'dllt' => $dtlt,
                'execution' => $execution,
                '_eventId' => $_evenId,
                'rmShown' => $rmShown,
            ],
            'http_errors'=>false,
        ]);
        $client->request('GET',"http://ehall.sdut.edu.cn/xsfw/sys/swpubapp/userinfo/getConfigUserInfo.do");
//        $client->request('GET','http://ehall.sdut.edu.cn/xsfw/sys/emappagelog/config/sswsapp.do');
//        $result = $client->request('GET',"http://ehall.sdut.edu.cn/xsfw/sys/sswsapp/modules/dorm_health_student/sswsxs_sswsxsbg.do",[
//            'http_errors'=>false,
//        ]);
        return $result->getBody();
    }
}

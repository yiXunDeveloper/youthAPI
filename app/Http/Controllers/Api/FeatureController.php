<?php

namespace App\Http\Controllers\Api;

use App\Models\QuesCategory;
use App\Models\QuesQuestion;
use App\Models\ServiceNewStudent;
use Illuminate\Http\Request;
class FeatureController extends Controller
{
    protected $cookie_file;
    function __construct()
    {

    }
    public function question()
    {
        $catid = \request('catid');
        $question= QuesCategory::find($catid);
        if(!count($question))
        {
            //   未找到该问卷
        }
        $logins = $question->fields;
        foreach ($logins as $login)
        {
            $login->input_options;
        }
        $invests = $question->questions;
        foreach ($invests as $invest)
        {
            $invest->input_options;
        }
        return $question;
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
    function http_request_get($url){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
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
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        if($use_cookie){
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
//            curl_setopt($curl, CURLOPT_COOKIE, 'JSESSIONID=6135581C8A078A9F173ED0D8B2ACC193');
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
        $this->cookie_file = storage_path('app/public/cookie.txt');
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
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://211.64.28.125/sdut_q/afterlog?code=code&state=QUERY');
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_file);  //保存cookie
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        $url = Storage::temporaryUrl(
//            'file.jpg', now()->addMinutes(5)
//        );
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}

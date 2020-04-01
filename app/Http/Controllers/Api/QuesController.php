<?php
/**
 * 问卷调查逻辑
 */

namespace App\Http\Controllers\Api;

use App\Models\QuesAdmin;
use App\Models\QuesAnswer;
use App\Models\QuesCategory;
use App\Models\QuesInvestOption;
use App\Models\QuesInvestQuestion;
use App\Models\QuesLoginOption;
use App\Models\QuesLoginQuestion;
use Auth;
use Carbon\Carbon;
use Dingo\Api\Exception\StoreResourceFailedException;
use Faker\Provider\ka_GE\DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\JWTAuth;
use Excel;
class QuesController extends Controller
{
    public function register(Request $request){
        $validator = app('validator')->make($request->all(),[
            'username'=>'required|unique:ques_admins',
            'password'=>'required'
        ]);
        if ($validator->fails) {
            throw new StoreResourceFailedException("数据非法",$validator->errors());
        }
        $username = $request->username;
        $password = bcrypt($request->password);
        QuesAdmin::create([
            'username'=>$username,
            'password'=>$password,
            'admin'=>0,
        ]);
        return $this->response->noContent()->setStatusCode(201);
    }
    public function login(Request $request){
        $username = $request->username;
        $password = $request->password;
        if ($user = QuesAdmin::where('username',$username)->first())
        {
            //账号密码匹配
            if (Hash::check($password,$user->password))
            {
                    $token = Auth::guard('ques')->setTTL(7200)->fromUser($user); //设置过期时间12小时60*12
                    return $this->respondWithToken($token)->setStatusCode(200);
            }else {
                //账号密码不匹配
                return $this->response->errorUnauthorized('密码错误');
            }
        }else{
            //未找到该用户
            return $this->response->errorUnauthorized('未找到该用户');
        }
    }
    //
    public function quesCreate(Request $request){
        $validator = app('validator')->make($request->all(),[
           'category'=>'array',
           'category.title'=>'required',
            'category.user_required'=>[
                Rule::in(['false', 'true']),
            ],
            'category.start_at'=>'required|date|before:category.end_at',
            'category.end_at'=>'required|date|after:category.start_at',
            'questions'=>'required|array',
            'questions.*.key'=>'required|unique:ques_invest_questions,key',
            'questions.*.input_num'=>'required|numeric',
            'questions.*.input_title'=>'required|string',
            'questions.*.input_type'=>[
                Rule::in([1,2,3]),
            ],
            'questions.*.is_required'=>[
                Rule::in(['true','false']),
            ],
            'validate_field'=>'required_if:category.user_required,true',
            'validate_field.*.key'=>'required_if:category.user_required,true|unique:ques_login_questions,key',
            'validate_field.*.input_title'=>'required_if:category.user_required,true',
            'validate_field.*.input_num'=>'required_if:category.user_required,true|numeric',
            'validate_field.*.input_type'=>[
                'required_if:category.user_required,true',
                Rule::in([0,1]),
            ],
            'validate_field.*.input_options'=>'nullable|array',
        ]);
//        return $request->validate_field;
        if ($validator->fails()){
            throw new StoreResourceFailedException("数据填写不完整或有错误",$validator->errors());
        }
        $user = Auth::guard('ques')->user();
        $category = $request->category;
        $category['author'] = $user->id;
        $questions = $request->questions;
        $options = $request->options;
        $validate_fields = $request->validate_field;
        if($category['user_required']=='false'){
            $category['user_required']=0;
        }else{
            $category['user_required']=1;
        }
        $cat = QuesCategory::create($category);
        if($questions){
            foreach ($questions as $question){
                if($question['is_required']=='false'){
                    $question['is_required'] = 0;
                }else{
                    $question['is_required'] = 1;
                }
                QuesInvestQuestion::create([
                    'key'=>$question['key'],
                    'catid'=>$cat->id,
                    'input_num'=>$question['input_num'],
                    'input_title'=>$question['input_title'],
                    'input_type'=>$question['input_type'],
                    'is_required'=>$question['is_required']
                ]);
            }
            foreach ($options as $option){
                QuesInvestOption::create($option);
            }
        }
        if($cat->user_required){
            foreach ($validate_fields as $validate_field){
                QuesLoginQuestion::create([
                    'key'=>$validate_field['key'],
                    'input_num' => $validate_field['input_num'],
                    'input_title'=> $validate_field['input_title'],
                    'input_type' => $validate_field['input_type'],
                    'catid'=>$cat->id,
                ]);
                if($validate_field['input_type']){
                    foreach ($validate_field['input_options'] as $option){
                        QuesLoginOption::create(array_add($option,'qkey',$validate_field['key']));
                    }
                }
            }
        }
        return $this->response->noContent()->setStatusCode(201);
    }
    public function quesDelete($id){
        $category = QuesCategory::find($id);
        $user = Auth::guard('ques')->user();
        if($category){
            if($user->admin == 1 || $user->id == $category->author){
                $invest_questions = $category->invest_questions;
                //先遍历问题删除所有选项
                foreach ($invest_questions as $invest_question){
                    $invest_question->options()->delete();
                }
                //再删除问题
                $category->invest_questions()->delete();
                $login_questions = $category->login_questions;
                if ($login_questions){
                    //先遍历问题删除所有选项
                    foreach ($login_questions as $login_question){
                        $login_question->input_options()->delete();
                    }
                }
                //再删除问题
                $category->login_questions()->delete();
                //删除答案
                $category->answers()->delete();
                $category->delete();
                return $this->response->noContent();
            }else{
                return $this->response->errorForbidden('您没有该权限');
            }
        }else{
            return $this->response->errorNotFound('资源未找到');
        }
    }
    public function quesGet(){
        $user = Auth::guard('ques')->user();
        if($user->admin==1){
            $categories = QuesCategory::all();
        }else{
            $categories = QuesCategory::where('author',$user->id)->get();
        }
        $cats = array();
        foreach ($categories as $category){
            $category->name = $category->user == null ? null : $category->user->name;
            array_push($cats,$category);
        }
        return $this->response->array(['data'=>$cats])->setStatusCode(200);
    }

    public function quesDetail($id){
        $category = QuesCategory::find($id);
        if(!$category){
            return $this->response->errorNotFound('问卷未找到');
        }
        $category->invest_questions;
        $category->login_questions;
        foreach ($category->invest_questions as $question){
            $question->options;
        }
        foreach ($category->login_questions as $login_question){
            $login_question->input_options;
        }
        return $this->response->array(['data'=>$category])->setStatusCode(200);
    }

    public function quesStore(Request $request,$id){
        $category = QuesCategory::find($id);
        $validator = app('validator')->make($request->all(),[
            'userinfo'=>'nullable|array',
            'answers'=>'required|array',
            'userinfo.*'=>'sometimes|required|string'
        ]);
        if ($validator->fails()) {
            throw new StoreResourceFailedException("您所提交的数据不符合规范，请检查后提交",$validator->errors());
        }
        $userinfo = $request->userinfo;
        $answers = $request->answers;
        $answers = new Collection($answers);
        if($category){
            $now = new \DateTime();
            $start = new \DateTime($category->start_at);
            $end = new \DateTime($category->end_at);
            if($now<$start||$now>$end) {
                return $this->response->errorForbidden('不在开放时间内');
            }
//            if(($category->user_required == 1 && count($userinfo) == 0)||count($answers)==0){
//                return $this->response->error('数据不合法',422);
//            }
            //数据验证
            $invest_questions = $category->invest_questions;//获取问卷问题
            if ($category->user_required ==1 ){  //如果要求验证用户身份：如年龄、政治面貌等
                $login_questions = $category->login_questions;  //获取验证问题
                foreach ($login_questions as $login_question){  //遍历验证问题
                    if ($login_question->input_type == 1){   //如果是单选
                        $login_options = $login_question->input_options;  //获取此问题的所有选项
                        $flag = 0;
                        foreach ($login_options as $login_option){   //遍历选项和提交的答案对比
                            if ($login_option->field_value == $userinfo[$login_question->input_num]){  //答案匹配到了选项中的一个值，通过验证
                                $flag = 1;
                                break;
                            }
                        }
                        if ($flag == 0){
                            throw new StoreResourceFailedException($login_question->input_title.'数据不合法');
                        }
                    }
                 }
            }
            foreach ($invest_questions as $invest_question){
                //非填空题必须填写
                if ($invest_question->input_type !=3 ) {
                    if (!isset($answers[$invest_question->input_num] )) {
                        throw new StoreResourceFailedException("第{$invest_question->input_num}题必须填写");
                    }
                }
                //如果是单选题  验证提交的答案是否在选项中
                if ($invest_question->input_type == 1){
                    $flag = 0;
                    foreach ($invest_question->options as $invest_option){
                        if ($answers[$invest_question->input_num] == $invest_option->field_value){
                            $flag = 1;
                            break;
                        }
                    }
                    if($flag == 0){
                        throw new StoreResourceFailedException($invest_question->input_title.'数据不合法');
                    }
                } else if($invest_question->input_type == 2) {
                    //如果是多选
                    //判断是否为数组
                    if (!is_array($answers[$invest_question->input_num])) {
                        throw new StoreResourceFailedException("第{$invest_question->input_num}题数据不合法");
                    }
                    //把选项用空格分隔开
                    $answers[$invest_question->input_num] = implode(" ",$answers[$invest_question->input_num]);
                }
            }
            QuesAnswer::create([
                'catid'=>$id,
                'userinfo'=>json_encode($userinfo),
                'answers'=>json_encode($answers),
            ]);
            return $this->response->noContent();
        }
       return $this->response->errorNotFound('问卷未找到');
    }
    public function quesExport($id){
        $user = Auth::guard('ques')->user();
        $category = QuesCategory::find($id);
        if(!$category){
            return $this->response->errorNotFound('问卷未找到');
        }
        if($user->id ==$category->author || $user->admin ==1){
            $answers = $category->answers()->get();   //获得全部提交的答案
            $invest_questions = $category->invest_questions()->get();  //获取问卷的问题
            $title = array();  //excel第一行
            $data =array();   //excel数据行
            if($category->user_required){
                //验证用户信息
                $login_questions = $category->login_questions()->get();   //获取登录问题
                $questions = array();
                foreach ($login_questions as $k => $login_question){
                    //如果问题是选择题，则把改问题的所有选项放到数组里   数据的键是 field_value(ABCD),数组的值是field_lable（计算机学院、经济学院）
                    if ($login_question->input_type == 1){   //单选
                        foreach ($login_question->input_options as $option) {
                            $questions[$k][$option->field_value] = $option->field_label;
                        }
                    }
                    array_push($title,$login_question->input_title);  //把登录问题添加到标题里
                }
                foreach ($answers as $answer){
                    $userinfos = json_decode($answer->userinfo,true);  //获取答案中的登录信息
                    $infos = array();
                    $ans = array_values(json_decode($answer->answers,true));  //答案中的问卷答案
                    foreach ($userinfos as $k => $v){  //遍历登录信息
                        //如果是选择题，把对应选项的描述添加进去
                        if($login_questions[$k-1]->input_type == 1){
                            array_push($infos,$questions[$k-1][$v]);
                        }else{
                            //填空题直接把答案添加进去
                            array_push($infos,$v);
                        }
                    }
                    unset($userinfos);//释放空间
                    array_push($data,array_merge($infos,$ans));
                }
            }else{
                //没有登录问题只有问卷
                foreach ($answers as $answer){
                    $ans = array_values(json_decode($answer->answers,true));
                    array_push($data,$ans);
                }
            }
            //文件题号加入到title里面
            foreach ($invest_questions as $invest_question){
                array_push($title,$invest_question->input_num);
            }
            //释放空间
            unset($answers);
            unset($invest_questions);
            Excel::create($category->title,function ($excel) use ($title,$data){
                $excel->sheet('sheet1',function ($sheet) use ($title,$data){
                    $sheet->rows($data);
                    $sheet->prependRow($title)->
                    row(1, function ($row) {
                        /** @var CellWriter $row */
                        $row->setFont(array(   //设置标题的样式
                            'family' => 'Calibri',
                            'size' => '16',
                            'bold' => true
                        ));
                    });

                });
            })->export('xlsx'); //xls格式会导致文件数据不完整
        }else{
            return $this->response->errorForbidden('您没有该权限');
        }
    }

    protected function respondWithToken($token)
    {
        return $this->response->array(['data'=>[
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('ques')->factory()->getTTL() * 60
        ],'errCode'=>200]);
    }

}

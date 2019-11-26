<?php

namespace App\Http\Controllers;
use Debugbar; //放在namespace下面。
use App\Models\PreordainList;
use App\Models\PreordainOpen;
use App\Models\QuesAnswer;
use App\Models\QuesCategory;
use Illuminate\Http\Request;

use Excel;

class FeatureController extends Controller
{
    //  测试文件，需要时再打开
    public function export($id){
        return;
        echo "初始: ".memory_get_usage()/1024/1024 ."MB\n";
        $category = QuesCategory::find($id);
        if(!$category){

            return $this->response->errorNotFound('问卷未找到');
        }


        $invest_questions = $category->invest_questions()->get();  //获取问卷的问题
        $title = array();  //excel第一行


        //如果登录前需要填写问题,遍历问题的选项存进数组
        $login_questions = null;
        $questions = null;
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
        }
        //问卷题号加入到title里面
        foreach ($invest_questions as $invest_question){
            array_push($title,$invest_question->input_num);
        }
        //释放空间
        unset($invest_questions);
        echo "循环前: ".memory_get_usage()/1024/1024 ."MB\n";
        Excel::create($category->title,function ($excel) use ($title,$category,$login_questions,$questions){
            Debugbar::info('this is a Info Message!');
            Debugbar::error('this is an Error Message!');
            Debugbar::warning('This is a Warning Message!');
            Debugbar::addMessage('Another Message', 'mylable');
            $excel->sheet('sheet1',function ($sheet) use ($title,$category,$login_questions,$questions){
                QuesAnswer::where('catid',$category->id)->chunk(1000,function ($answers) use ($sheet,$category,$login_questions,$questions){
                    echo "使用: ".memory_get_usage()/1024/1024 ."MB\n";
                    $data = array();  //data数组用来存用户答案数据
                    //导出用户答案
                    //如果需要用户验证
                    if ($category->user_required) {
                        foreach ($answers as $answer) {
                            $userinfos = json_decode($answer->userinfo, true); //获取答案中的登录信息
                            $infos = array();
                            $ans = array_values(json_decode($answer->answers, true)); //获取答案中的问卷答案
                            foreach ($userinfos as $k => $v) { //遍历登录信息
                                //如果是选择题，把对应选项的描述添加进去
                                if ($login_questions[$k - 1]->input_type == 1) {
                                    array_push($infos, $questions[$k - 1][$v]);
                                } else {
                                    //填空题直接把答案添加进去
                                    array_push($infos, $v);
                                }
                            }
                            unset($userinfos); //释放空间
                            array_push($data, array_merge($infos, $ans));
                            unset($infos);
                            unset($ans);
                        }
                    }else {
                        //没有登录问题只有问卷
                        foreach($answers as $answer) {
                            $ans = array_values(json_decode($answer->answers, true));
                            array_push($data, $ans);
                        }
                    }
                    $sheet->rows($data);
                    unset($data);


//                    echo "循环完: ".memory_get_usage()/1024/1024 ."MB\n";
//                    unset($sheet);
//                    echo "释放后: ".memory_get_usage()/1024/1024 ."MB\n";
//                    dd(123);
                });
                echo "循环完: ".memory_get_usage()/1024/1024 ."MB\n";
                echo "峰值: ".memory_get_peak_usage()/1024/1024 ."MB\n";
                $sheet->prependRow($title)->
                row(1, function ($row) {
                    /** @var CellWriter $row */
                    $row->setFont(array(   //设置标题的样式
                        'family' => 'Calibri',
                        'size' => '16',
                        'bold' => true
                    ));
                });

            })->export('xlsx');
        }); //xls格式会导致文件数据不完整
    }
}

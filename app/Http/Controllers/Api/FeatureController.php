<?php

namespace App\Http\Controllers\Api;

use App\Models\QuesCategory;
use App\Models\QuesQuestion;
use App\Models\ServiceNewStudent;
use Illuminate\Http\Request;
class FeatureController extends Controller
{
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
       if(strlen($num)==18)
       {
           $new_student = ServiceNewStudent::where('id_card',$num)->first();
       }else
       {
           $new_student = ServiceNewStudent::where('kaohao',$num)->first();
       }
       if($new_student)
       {
           return $this->response->array([
               'name'=>$new_student->name,
               'sdut_id'=>$new_student->sdut_id,
               'college'=>$new_student->college,
               'major'=>$new_student->major,
               'class'=>$new_student->class,
               'xuezhi'=>$new_student->xuezhi,
               'kaohao'=>$new_student->kaohao
           ])->setStatusCode(200);
       }else{
           return $this->response->errorNotFound('没有该学生信息');
       }
    }
}

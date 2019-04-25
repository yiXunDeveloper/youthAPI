<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
                if ($this->info_type == 'information') {
                    return [
                        'name' => 'required|string',
                        'political_status' => 'required|string',
                        'birthplace' => 'required|string',
                        'now_work_place' => 'required|string',
                        'highest_education' => 'required|string',
                        'professional_code' => 'required|string',
                        'graduated_time' => 'required|date',
                        'sex' => 'required|string',
                        'nation' => 'required|string',
                        'marriage' => 'required|boolean',
                        'file_unit' => 'required|string',
                        'highest_degree' => 'required|string',
                        'graduated_school' => 'required|string',
                        'birth_year' => 'required|digits:value',
                        'learn_subject' => 'required|string',
                        'apply_position' => 'required|string',
                        'is_graduates' => 'required|string',
                        'id_nb' => 'required|size:18',
                        'phone' =>'required',
                        'email'=>'required|email',
                        'avatar'=>'required|digits_between:1,5',
                        'birth_mouth' =>'required|digits_between:1,2'
                    ];
                } elseif ($this->info_type == 'learn') {
                    return [
                        'join_time' => 'required|date',
                        'graduate_time' => 'required|date',

                        'graduate_school' => 'required',
                        'major' => 'required|string',
                        'education' => 'required',
                        'bachelor' => 'required',
                        'learn_way' => 'required',
                    ];
                } elseif ($this->info_type == 'work') {
                    return [
                        'join_time' => 'required|date',
                        'drop_time' => 'required|date',
                        'company' => 'required',
                        'position' => 'required',
                    ];
                } elseif ($this->info_type == 'ben_position' || $this->info_type == 'yan_position') {
                    return [
                        'begin_time' => 'required|date',
                        'over_time' => 'required|date',

                        'witness_name' => 'required',
                        'witness_position' => 'required',
                        'witness_phone' => 'required',
                        'position' => 'required',
                    ];
                } elseif ($this->info_type == 'honour') {
                    return [
                        'get_time' => 'required|date',
                        'honour_name' => 'required',
                    ];
                }elseif($this->info_type == 'education'){
                     return [
                        'ben_code' => 'required',
                        'xue_code' => 'required',
                    ];
                }

        }
public function attributes(){
    return [
                        'name' => '姓名',
                        'political_status' => '政治面貌',
                        'birthplace' => '籍贯',
                        'now_work_place' => '现在工作单位',
                        'highest_education' => '最高学历',
                        'professional_code' => '研究生报考专业代码',
                        'graduated_time' => '毕业时间',
                        'sex' => '性别',
                        'nation' => '民族',
                        'marriage' => '婚姻状况',
                        'file_unit' => '档案所在单位',
                        'highest_degree' => '最高学位',
                        'graduated_school' => '毕业学校',
                        'birth_year' => '出生年份',
                        'learn_subject' => '所学专业',
                        'apply_position' => '应聘岗位',
                        'is_graduates' => '是否应届毕业生',
                        'id_nb' => '身份证号码',
                        'phone' =>'联系电话',
                        'email'=>'E-mail',
                        'avatar'=>'照片',
                        'join_time' => '加入时间',
                        'graduate_time' => '毕业时间',
                        'graduate_school' => '毕业学校',
                        'major' => '所学专业',
                        'education' => '学历',
                        'bachelor' => '学位',
                        'learn_way' => '学习形式',
                        'drop_time' => '离职时间',
                        'company' => '工作单位',
                        'position' => '职务或职位',
                        'begin_time' => '开始时间',
                        'over_time' => '结束时间',
                        'witness_name' => '担任职务',
                        'witness_position' => '证明人姓名',
                        'witness_phone' => '证明人职务',
                        'position' => '证明人联系电话',
                        'get_time' => '获奖时间',
                        'honour_name' => '荣誉称号',
                        'ben_code' => '本科毕业证书编号',
                        'xue_code' => '学士学位证书编号',


        
        ];
    }

}

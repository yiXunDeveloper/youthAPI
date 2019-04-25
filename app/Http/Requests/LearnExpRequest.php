<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LearnExpRequest extends FormRequest
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
        return [
            'join_time' =>  'required',
            'graduate_time' => 'required',
            'graduate_school' =>  'required|string',
            'major' =>  'required|string',
            'education' =>  'required',
            'bachelor' =>  'required',
            'learn_way' =>  'required',
        ];
    }
    public function attributes()
    {
        return [
            'join_time' => '入学时间',
            'graduate_time' => '毕业时间',
            'graduate_school' =>  '毕业学校',
            'major' =>  '所学专业',
            'education' =>  '学历',
            'bachelor' =>  '学位',
            'learn_way' =>  '学习形式',
        ];
    }
}

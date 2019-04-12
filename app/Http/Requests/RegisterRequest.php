<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'id_nb' => 'required|size:18|unique:recruit_users,id_nb',
            'password' => 'required|between:6,16|confirmed',
            'phone' =>'required|unique:recruit_users,phone',
            'name' =>'required|between:2,16',
            'captcha_key' => 'required|string',
            'email'=>'required|email',
            'captcha_code' => 'required|string',
        ];
    }
    public function attributes()
    {
        return [
            'captcha_key' => '图片验证码 key',
            'captcha_code' => '图片验证码',
            'id_nb' => '身份证号'
        ];
    }
    public function messages()
    {
        return [
            'name.between' => '用户名必须介于 6 - 16 个字符之间。',
            'name.required' => '用户名不能为空。',
            'id_nb.required' => '身份证号不能为空',
            'id_nb.size' => '身份证号位数有误',
            'id_nb.unique' => '身份证号已被占用',
        ];
    }
}

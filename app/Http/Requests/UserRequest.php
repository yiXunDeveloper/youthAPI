<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'pictures' => 'mimes:jpeg,bmp,png,gif|dimensions:min_width=200,min_height=200',
            'type'=>'required'
        ];
    }
    public function messages()
    {
        return [
            'pictures.mimes' =>'头像必须是 jpeg, bmp, png, gif 格式的图片',
            'pictures.dimensions' => '图片的清晰度不够，宽和高需要 200px 以上',
        ];
    }
}

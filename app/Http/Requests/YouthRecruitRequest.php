<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class YouthRecruitRequest extends FormRequest
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
            'name'=>'required|max:20',
            'sex'=>'max:4',
            'nb'=>'required|max:11|min:11',
            'phone'=>'required|max:11|min:11',
            'email'=>'email',
            'college'=>'required|max:20',
            'class'=>'required|max:20',
            'part_1'=>'required|max:20',
            'part_2'=>'max:20',
            'introduction'=>'required',
        ];
    }
}

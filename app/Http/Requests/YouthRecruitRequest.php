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
            'name'         => 'required|min:2|max:20',
            'sex'          => 'required|max:1',
            'nb'           => 'required|max:11|min:11',
            'phone'        => 'required|max:11|min:11',
            'email'        => 'nullable|email',
            'college'      => 'required|max:2',
            'class'        => 'required|max:20',
            'part_1'       => 'required|max:2',
            'part_2'       => 'required|max:2',
            'introduction' => 'required',
        ];
    }
}

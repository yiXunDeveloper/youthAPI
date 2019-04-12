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
        return [
            'name' => 'required|string',
            'political_status' => 'required|string',
            'birthplace' => 'required|string',
            'now_work_place' => 'required|string',
            'highest_education' => 'required|string',
            'professional_code' => 'required|string',
            'graduated_time' => 'required|string',
            'sex' => 'required|string',
            'nation' => 'required|string',
            'marriage' => 'required|string',
            'file_unit' => 'required|string',
            'highest_degree' => 'required|string',
            'graduated_school' => 'required|string',
            'birth_year' => 'required|string',
            'learn_subject' => 'required|string',
            'apply_position' => 'required|string',
            'is_graduates' => 'required|string',
        ];
    }
}

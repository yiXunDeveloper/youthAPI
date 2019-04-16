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
                } elseif ($this->info_type == 'learn') {
                    return [
                        'join_time' => 'required',
                        'graduate_time' => 'required',
                        'graduate_school' => 'required',
                        'major' => 'required|string',
                        'education' => 'required',
                        'bachelor' => 'required',
                        'learn_way' => 'required',
                    ];
                } elseif ($this->info_type == 'work') {
                    return [
                        'join_time' => 'required',
                        'drop_time' => 'required',
                        'company' => 'required',
                        'position' => 'required',
                    ];
                } elseif ($this->info_type == 'ben_position') {
                    return [
                        'begin_time' => 'required',
                        'over_time' => 'required',
                        'witness_name' => 'required',
                        'witness_position' => 'required',
                        'witness_phone' => 'required',
                        'position' => 'required',
                    ];
                } elseif ($this->info_type == 'yan_position'){
                    return [
                        'begin_time' => 'required',
                        'over_time' => 'required',
                        'witness_name' => 'required',
                        'witness_position' => 'required',
                        'witness_phone' => 'required',
                        'position' => 'required',
                    ];
                } elseif ($this->info_type == 'honour') {
                    return [
                        'get_time' => 'required',
                        'honour_name' => 'required',
                    ];
                }elseif ($this->info_type == 'education') {
                    return [
                        'ben_code' => 'required',
                        'xue_code' => 'required',
                ];
                }
        }
}

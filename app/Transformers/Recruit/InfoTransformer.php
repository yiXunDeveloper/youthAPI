<?php

namespace App\Transformers\Recruit;

use App\Models\Recruit\Information;
use League\Fractal\TransformerAbstract;
use App\Models\Recruit\User;

class InfoTransformer extends TransformerAbstract
{
    public function transform(Information $information)
    {   $user = User::find($information->user_id);
        return [
            'id' => $information->id,
            'political_status' => $information->political_status,
            'birthplace' => $information->birthplace,
            'now_work_place' => $information->now_work_place,
            'highest_education' => $information->highest_education,
            'professional_code' => $information->professional_code,
            'graduated_time' => $information->graduated_time,
            'sex' => $information->sex,
            'nation' => $information->nation,
            'marriage' => $information->marriage,
            'file_unit' => $information->file_unit,
            'highest_degree' => $information->highest_degree,
            'graduated_school' => $information->graduated_school,
            'birth_year' => $information->birth_year,
            'birth_mouth' => $information->birth_mouth,
            'learn_subject' => $information->learn_subject,
            'apply_position' => $information->apply_position,
            'is_graduates' => $information->is_graduates,
            'user_id' => $information->user_id,
            'name' => $user->name,
            'email' => $user->email,
//            'last_actived_at' => $user->last_actived_at->toDateTimeString(),
            'created_at' => $user->created_at->toDateTimeString(),
            'updated_at' => $user->updated_at->toDateTimeString(),
            'avatar' =>$user->avatar($user->avatar_id)->path,
        ];
    }
}
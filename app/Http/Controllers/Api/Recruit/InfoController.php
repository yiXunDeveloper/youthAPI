<?php

namespace App\Http\Controllers\Api\Recruit;

use App\Http\Requests\InfoRequest;
use App\Models\Recruit\Image;
use App\Models\Recruit\Information;
use App\Transformers\Recruit\InfoTransformer;
use Dingo\Api\Auth\Auth;

class InfoController extends Controller
{
    public function infoStore(InfoRequest $request)
    {
        $user = Information::create([

            'political_status' => $request->political_status,
            'birthplace' => $request->birthplace,
            'now_work_place' => $request->now_work_place,
            'highest_education' => $request->highest_education,
            'professional_code' => $request->professional_code,
            'graduated_time' => $request->graduated_time,
            'sex' => $request->sex,
            'nation' => $request->nation,
            'marriage' => $request->marriage,
            'file_unit' => $request->file_unit,
            'highest_degree' => $request->highest_degree,
            'graduated_school' => $request->graduated_school,
            'birth_year' => $request->birth_year,
            'birth_mouth' => $request->birth_mouth,
            'learn_subject' => $request->learn_subject,
            'apply_position' => $request->apply_position,
            'is_graduates' => $request->is_graduates,
            'user_id'=>\Auth::guard('recruit')->user()->id,
        ]);
        $users = \Auth::guard('recruit')->user();
        $attributes = $request->only(['email', 'phone','id_nb','avatar_id','name']);
        $users->update($attributes);
//        dd($user->id);
        return $this->response->item($user,new InfoTransformer());
    }
}

<?php

namespace App\Transformers;


use App\Models\QqArticle;
use App\Models\QqFans;
use App\Models\QqUser;
use App\Models\QqUserBasic;
use Auth;
use League\Fractal\TransformerAbstract;

class QqUserTransformer extends TransformerAbstract
{
    public function transform(QqUser $user)
    {
        $data = QqUserBasic::where('user_id',$user->id)->first();
        return [
            'id' => $user->id,
            'school' => $data->school,
            'offical' => $data->offical,
            'sex' => $data->sex,
            'des' => $data->des,
            'tags' => $data->tags,
            'level'=>$data->level,
            'gender'=>$data->gender,
            'language'=>$data->language,
            'city'=>$data->city,
            'province'=>$data->province,
            'country'=>$data->country,
            'name'=>$data->name,
            'nickName'=>$user->nickName,
            'avatarUrl'=>$user->avatarUrl,
            'last_actived_at' => $user->last_actived_at,
            'created_at' => $user->created_at->toDateTimeString(),
            'updated_at' => $user->updated_at->toDateTimeString(),
            'count_fans'=>count(QqFans::where('fans_id',Auth::guard('qq')->user()->id)->get()),
            'count_faned'=>count(QqFans::where('user_id',Auth::guard('qq')->user()->id)->get()),
            'count_article'=>count(QqArticle::where('user_id',Auth::guard('qq')->user()->id)->get())
        ];
    }
}
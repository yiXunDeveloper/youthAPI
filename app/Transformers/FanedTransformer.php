<?php

namespace App\Transformers;

use App\Http\Controllers\Qq\Article;
use App\Models\Picture;
use App\Models\QqArticle;
use App\Models\QqArticleGood;
use App\Models\QqComment;
use App\Models\QqFans;
use App\Models\QqUser;
use Auth;
use Doctrine\DBAL\Schema\Schema;
use League\Fractal\TransformerAbstract;

class FanedTransformer extends TransformerAbstract
{
    public function transform(QqFans $fans)
    {
        return [
            'fansList' => $this->users($fans->user_id),
            ];
    }
    public function users($user_id){
        $user_info = QqUser::find($user_id);
        return [
            'user_id'=>$user_info->id,
            'nickname'=>$user_info->nickName,
            'avatarUrl'=>$user_info->avatarUrl
        ];
    }

}
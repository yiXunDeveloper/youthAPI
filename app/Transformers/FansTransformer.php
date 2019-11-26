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

class FansTransformer extends TransformerAbstract
{
    public function transform(QqFans $fans)
    {
        return [
            'fanedList' => $this->users($fans->fans_id),
            ];
    }
    public function users($imga){
        $imga = QqUser::find($imga);
        return [
            'user_id'=>$imga->id,
            'nickname'=>$imga->nickName,
            'avatarUrl'=>$imga->avatarUrl
        ];
    }

}
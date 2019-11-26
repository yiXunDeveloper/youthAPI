<?php

namespace App\Transformers;

use App\Http\Controllers\Qq\Article;
use App\Models\Picture;
use App\Models\QqArticle;
use App\Models\QqArticleGood;
use App\Models\QqComment;
use App\Models\QqUser;
use App\Models\QqUserBasic;
use Auth;
use Doctrine\DBAL\Schema\Schema;
use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    public function transform(QqArticle $article)
    {
        return [
            'id' => $article->id,
            'content' => $article->content,
            'created_at' => $article->created_at->toDateTimeString(),
            'user_info' => $this->users($article->user_id),
            'pictures' => $this->ImgTransformer($article->pictures),
            'count_comment' => count(QqComment::where('article_id', $article->id)->get()),
            'count_zan' => count(QqArticleGood::where('article_id', $article->id)->get()),
            'is_zan' => is_null(QqArticleGood::where('article_id', $article->id)->where('user_id', Auth::guard('qq')->user()->id)->first()) ? 0 : 1,
            'type' => $article->type,
        ];
    }
    public function users($imga)
    {
        $imga = QqUser::find($imga);
         $imgas = QqUserBasic::where('user_id',$imga->id)->first();
        return [
            'user_id' => $imga->id,
            'nickName' => $imga->nickName,
            'avatarUrl' => $imga->avatarUrl,
            'gender' => $imgas->gender,
        ];
    }
    public function ImgTransformer($imga)
    {
        $array = [];
        if (!is_null($imga)) {
            foreach (json_decode($imga) as $key => $item) {
                $img = Picture::find($item);
                $array[$key] = $img->path;
            }
        }
        return $array;
    }
}

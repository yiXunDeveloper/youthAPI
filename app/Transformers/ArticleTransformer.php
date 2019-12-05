<?php

namespace App\Transformers;

use Auth;

use App\Models\QqUser;
use App\Models\Picture;
use App\Models\QqArticle;
use App\Models\QqComment;
use App\Models\QqUserBasic;
use App\Models\QqArticleGood;
use Doctrine\DBAL\Schema\Schema;
use App\Http\Controllers\Qq\Article;
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
    public function users($user_id)
    {
        $user_info = QqUser::find($user_id);
        $user_basic_info = QqUserBasic::where('user_id',$user_info->id)->first();
        return [
            'user_id' => $user_basic_info->id,
            'nickName' => $user_basic_info->nickName,
            'avatarUrl' => $user_basic_info->avatarUrl,
            'gender' => $user_basic_info->gender,
        ];
    }
    public function ImgTransformer($image)
    {
        $array = [];
        if (!is_null($image)) {
            foreach (json_decode($image) as $key => $item) {
                $img = Picture::find($item);
                $array[$key] = $img->path;
            }
        }
        return $array;
    }
}

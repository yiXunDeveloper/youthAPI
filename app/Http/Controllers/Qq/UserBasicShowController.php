<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QqArticle;
use App\Models\QqArticleGood;
use App\Models\QqComment;

class UserBasicShowController extends Controller
{
    /**
     * 该方法返回个人文章信息
     * +评论总数
     * +获赞总数
     */
    public function getPersonallyPublishedArticles()
    {
        $operating_user = $this->user()->id;
        $articleData = QqArticle::where('user_id', $operating_user)
            ->orderBy('id', 'DESC')
            ->get();
        $commentTotal = QqComment::where('user_id', $operating_user)
            ->count();
        $praiseCount = 0;
        foreach ($articleData as $key => $value) {
            $articleId = $value->id;
            $perCount = QqArticleGood::where('id', $articleId)
                ->count();
            $praiseCount += $perCount;
        }

        return response()->json([
            'articleData'->$articleData,
            'commentTotal' => $commentTotal,
            'praiseCount' => $praiseCount
        ], 200);
    }

    /**
     * 该方法返回评论相关信息
     * 评论内容+评论者
     */
    public function getCommentAboutInfo($articleId)
    {
        $commentData = QqComment::where('article_id', articleId)
            ->get();
        foreach ($commentData as $key => $value) {
            $value->QqUserBasic;
        }

        return response()->json([
            'commentData' => $commentData
        ], 200);
    }

    /**
     * 点赞者信息
     */

    public function getGoodAboutInfo($articleId)
    {
        $likeData = QqArticleGood::where('article_id', $articleId)
            ->get();
        foreach ($likeData as $key => $value) {
            $value->QqUserBasic;
        }

        return response()->json([
            'likeData' => $likeData
        ], 200);
    }
}

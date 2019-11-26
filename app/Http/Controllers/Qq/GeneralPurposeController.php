<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Models\QqArticle;
use App\Models\QqArticleGood;
use App\Models\QqUserBasic;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\QqComment;

class GeneralPurposeController extends Controller
{

    /**
     * 获取首页热点文章信息+热点文章作者信息+评论数+点赞数
     */
    public function getHomeArticleListBasicInfo()
    {
        $ArticleListInfo = QqArticle::orderBy('id', 'DESC')->paginate(15);

        foreach ($ArticleListInfo as $key => $value) {
            $value->author;
            //获取当前文章的id
            $currentArticleId = $value->id;
            //统计点赞数
            $countGood[$currentArticleId] =
                QqArticleGood::where('article_id', $currentArticleId)
                ->count();
            //统计评论数
            $countComment[$currentArticleId] =
                QqComment::where('article_id', $currentArticleId)
                ->count();
        }
        return response()->json([
            "data" => $ArticleListInfo,
            "countGood" => $countGood,
            "countComment" => $countComment,
            "messge" => "Get Successfully"
        ], 200);
    }

    /**
     * 获取根据Type分类后的文章列表 返回信息上同
     */
    public function getHomeArticleListBasicInfoByType($Type)
    {
        $ArticleBytypeListInfo = QqArticle::where('type', $Type)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($ArticleBytypeListInfo as $key => $value) {
            $value->author;
            //获取当前文章的id
            $currentArticleId = $value->id;
            //统计点赞数
            $countGood[$currentArticleId] =
                QqArticleGood::where('article_id', $currentArticleId)
                ->count();
            //统计评论数
            $countComment[$currentArticleId] =
                QqComment::where('article_id', $currentArticleId)
                ->count();
        }

        return response()->json([
            "data" => $ArticleBytypeListInfo,
            "countGood" => $countGood,
            "countComment" => $countComment,
            "messge" => "Get Successfully"
        ], 200);
    }

    /**
     * 获取热点文章评论的相关信息
     */
    public function getArticleCommentMainInfo($articleId)
    {
        $currentArticleCommentAboutInfo =
            QqComment::where('article_id', $articleId)
            ->get();

        foreach ($currentArticleCommentAboutInfo as $key => $value) {
            $value->QqUserBasic;
        }

        return response()->json([
            "data" => $currentArticleCommentAboutInfo,
            "messge" => "Get Successfully"
        ], 200);
    }

    /**
     * 获取热点文章点赞的相关信息
     */
    public function getArticleGoodMainInfo($articleId)
    {
        $currentArticleGoodAboutInfo =
            QqArticleGood::where('article_id', $articleId)
            ->get();
        foreach ($currentArticleGoodAboutInfo as $key => $value) {
            $value->QqUserBasic;
        }

        return response()->json([
            "data" => $currentArticleGoodAboutInfo,
            "messge" => "Get Successfully"
        ], 200);
    }

    protected function respond($code,$message,$data=null)
    {
        return $this->response->array([
            'code'=>$code,
            'data'=>$data,
            'message'=>$message
        ]);
    }
}

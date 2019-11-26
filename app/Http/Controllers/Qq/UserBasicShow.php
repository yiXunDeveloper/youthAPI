<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Models\QqArticle;
use App\Models\QqArticleGood;
use App\Models\QqComment;
use App\Models\QqFans;

class UserBasicShow extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * 获取个人发布动态总数
         * +个人关注用户总数
         * +个人评论总数
         * +个人赞过的文章总数
         * +个人获赞总数
         */
        //找到当前用户
        $operating_user = $this->user()->id;
        $dynamicTotal = QqArticle::where('user_id', $operating_user)
            ->count();
        $concernedTotal = QqFans::where('fans_id', $operating_user)
            ->count();
        $commentTotal = QqComment::where('user_id', $operating_user)
            ->count();
        $likeTotal = QqArticleGood::where('user_id', $operating_user)
            ->count();
        $articleData = QqArticle::where('user_id', $operating_user)->get();
        $praiseCount = 0;
        foreach ($articleData as $key => $value) {
            $articleId = $value->id;
            $perCount = QqArticleGood::where('id', $articleId)
                ->count();
            $praiseCount += $perCount;
        }
        
        return response()->json([
            'dynamicTotal' => $dynamicTotal,
            'concernedTotal' => $concernedTotal,
            'commentTotal' => $commentTotal,
            'likeTotal' => $likeTotal,
            'praiseCount' => $praiseCount
        ], 200);
    }
}

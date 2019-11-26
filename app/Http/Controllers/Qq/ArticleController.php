<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QqArticle;

class ArticleController extends Controller
{
    //获取全部文章列表   主页面展示
//    public function getAllArticle() {
//
//        $data = QqArticle::get();
//
//        return response()->json($data, 200);
//    }

    //根据Type类型来分类获取对应的文章    文章分类
    public function getAllArticleByType($type) {

        $data = QqArticle::where('type', $type)->get();

        return response()->json($data, 200);
    }
}

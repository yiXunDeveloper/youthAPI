<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QqArticle;

class CommentController extends Controller
{
    public function getPerArticleCom($id)
    {
        //根据文章id获取当前文章
        $currentArticleInfo = QqArticle::where('id', $id)->get();

        return response()->json([
            
            "messge" => "Get Successfully"
        ], 200);
    }
}

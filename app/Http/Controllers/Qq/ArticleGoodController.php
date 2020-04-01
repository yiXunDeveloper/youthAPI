<?php

namespace App\Http\Controllers\Qq;

use App\Models\QqArticle;
use App\Models\QqArticleGood;
use Illuminate\Http\Request;


class ArticleGoodController extends Controller
{
    public function zan($id)
    {
        $article = QqArticleGood::where('article_id',$id)->first();
        if($article){
            $article = $article->delete();
            if($article){
                return $this->respond(1,'取消赞成功');
            }
        }else{
            $data['user_id'] = $this->user()->id;
            $data['article_id'] = $id;
            $data['comment_id'] = null;
            $article = QqArticleGood::create($data);
            if($article){
                return $this->respond(1,'点赞成功');
            }
        }
    }

    public function zanedList() {
        // $user_id = $this->user()->id;
        // $article = QqArticle::where('user_id', )->count();
        $article = new QqArticle();
        $article = $article->orderBy('created_at','DESC')->paginate(10);
        return $this->response->paginator($article, new ArticleTransformer());
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

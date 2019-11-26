<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Models\QqArticle;
use App\Models\QqCollect;
use App\Transformers\ArticleTransformer;

// use App\Http\Controllers\Controller;

class CollectController extends Controller
{

    public function collectOrNot($article_id)
    {
        $operating_user = $this->user()->id;
        $data = QqCollect::where('article_id', $article_id)
            ->where('user_id', $operating_user)
            ->first();
        if ($data) {
            $data = $data->delete();
            if ($data) {
                return $this->respond(1, '取消收藏成功');
            }
        } else {
            $data['article_id'] = $article_id;
            $data['user_id'] = $this->user()->id;
            $data = QqCollect::create($data);
            return $this->respond(1, '收藏成功');
        }
    }

    public function collectionList()
    {
        $countCollects = QqCollect::where('user_id', $this->user()->id)->pluck('article_id')->toArray();
        $collect = new QqArticle();
        $collect = $collect->whereIn('id', $countCollects)->orderBy('created_at', 'DESC')->paginate(10);
        return $this->response->paginator($collect, new ArticleTransformer());
    }



    protected function respond($code, $message, $data = null)
    {
        return $this->response->array([
            'code' => $code,
            'data' => $data,
            'message' => $message
        ]);
    }
}

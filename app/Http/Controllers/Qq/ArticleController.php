<?php

namespace App\Http\Controllers\Qq;

use Auth;
use App\Models\Image;
use App\Models\QqUser;
use App\Models\Picture;
use App\Models\Artical;
use App\Models\QqArticle;
use App\Models\QqComment;
use App\Models\QqUserBasic;
use Illuminate\Http\Request;
use App\Models\QqArticleGood;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserRequest;
use App\Handlers\ImageUploadHandler;
use App\Transformers\ArticleTransformer;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ArticalUpdateRequest;


class ArticleController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function articleList()
    {

        $article = new QqArticle();
        $article = $article->orderBy('created_at', 'DESC')->paginate(10);
        return $this->response->paginator($article, new ArticleTransformer());
    }
    public function zanArticle()
    {
        $zans = QqArticleGood::where('user_id', $this->user()->id)->pluck('article_id')->toArray();
        $article = new QqArticle();
        $article = $article->whereIn('id', $zans)->orderBy('created_at', 'DESC')->paginate(10);
        return $this->response->paginator($article, new ArticleTransformer());
    }
    public function hotArticle()
    {
        //$zans = QqArticleGood::where('article_id', $this->user()->id)->pluck('article_id')->toArray();
        $article = new QqArticle();
        $article = $article->pluck('id')->toArray();
        foreach ($article as $key=>$item){
            $array[$key]['article_id'] = $item;
            $array[$key]['article_zan'] = count(QqArticleGood::where('article_id',$item)->get());
        }

        $zans = array_column($array,'article_zan');
        array_multisort($zans,SORT_DESC,$array);
        $arr2 = array_column($array, 'article_id');
        $article = QqArticle::whereIn('id',$arr2)
            ->orderBy(DB::raw('FIND_IN_SET(id, "' . implode(",", $arr2) . '"' . ")"))
            ->orderBy('created_at', 'DESC')->paginate(10);
        return $this->response->paginator($article, new ArticleTransformer());
    }
    public function typeArticleList(Request $request)
    {

        $article = new QqArticle();
        $article = $article->where('type', $request->type)->orderBy('created_at', 'DESC')->paginate(10);
        return $this->response->paginator($article, new ArticleTransformer());
    }
    public function meArticle($id)
    {

        $article = new QqArticle();
        if ($id != 'me') {
            $article = $article->where('user_id', $id)->orderBy('created_at', 'DESC')->paginate(10);
        } else {
            $article = $article->where('user_id', $this->user()->id)->orderBy('created_at', 'DESC')->paginate(10);
        }
        return $this->response->paginator($article, new ArticleTransformer());
    }
    public function store(Request $request)
    {
        if ($request->content) {
            $data['content'] = $request->content;
        }
        $data = $request->only(['content']);
        if ($request->type) {
            $data['type'] = $request->type;
        }
        $data['user_id'] = $this->user()->id;
        if ($request->pictures) {
            $data['pictures'] = json_encode($request->pictures);
        }
        $data = QqArticle::create($data);
        if ($data) {
            $imgs = array();
            if ($data->pictures) {
                foreach (json_decode($data->pictures) as $key => $item) {
                    $img = Picture::find($item);
                    $imgs[$key] = $img->path;
                }
            }
            $data = array([
                'id' => $data->id,
                'content' => $data->content,
                'user_id' => $data->user_id,
                'pictures' => $imgs
            ]);
        }
        return $this->respond(1, '创建成功', $data)->setStatusCode(200);
    }

    public function pictStore(ImageUploadHandler $handler, UserRequest $request)
    {
        if ($request->pictures) {
            $result = $handler->save($request->pictures, $request->type, $this->user()->id, $request->max_width);
            if ($result) {
                $data = $request->only(['type']);
                $data['path'] = $result['path'];
                //$data['mini_path'] = $result['mini_path'];
                $data['user_id'] = $this->user()->id;
                $data['filename'] = '0';
                $picture =  Picture::create($data);
            }
            if ($picture) {
                $pictures['mini_path'] = $picture->mini_path;
                $pictures['path'] = $picture->path;
                $pictures['id'] = $picture->id;
                return $this->respond('1', '上传成功', $pictures);
            } else {
                return $this->respond('0', '失败稍后重试');
            }
        }
    }
    public function show($id)
    {
        //根据id查询文章
        $data = QqArticle::find($id);
        if (is_null($data)) {
            return $this->respond(0, '文章或已被删除')->setStatusCode(200);
        }
        if ($data) {
            $imgs = array();
            if ($data->pictures) {
                foreach (json_decode($data->pictures) as $key => $item) {
                    $img = Picture::find($item);
                    $imgs[$key] = $img->path;
                }
            }
            $comments = array();
            $comment = QqComment::where('article_id', $id)->get();
            if ($comment) {
                $comments = $comment;
            }
            $data = array([
                'id' => $data->id,
                'content' => $data->content,
                'user_id' => $data->user_id,
                'pictures' => $imgs,
                'comment' => $comments
            ]);
        }
        return $this->respond(1, '返回成功', $data)->setStatusCode(200);
    }
    public function update(ArticalUpdateRequest $request)
    {
        $data = QqArticle::find($request->id);
        if ($data) {
            if ($data->user_id == $this->user()->id) {
                $date = $request->only(['content']);
                if ($request->pictures) {
                    $date['pictures'] = json_encode($request->pictures);
                } else {
                    $data['pictures'] = null;
                }
                if ($request->type) {
                    $data['type'] = $request->type;
                }
                $data = $data->update($date);
                if ($data) {
                    $imgs = array();
                    $data = QqArticle::find($request->id);
                    if ($data->pictures) {
                        foreach (json_decode($data->pictures) as $key => $item) {
                            $img = Picture::find($item);
                            $imgs[$key] = $img->path;
                        }
                    }
                    $data = array([
                        'id' => $data->id,
                        'content' => $data->content,
                        'user_id' => $data->user_id,
                        'pictures' => $imgs
                    ]);
                }
                return $this->respond(1, '成功更新', $data)->setStatusCode(200);
            } else {
                return $this->respond('0', '无权限修改')->setStatusCode(200);
            }
        }
    }

    public function delete($id)
    {

        $data = QqArticle::find($id);
        if ($data) {
            if ($data->user_id == $this->user()->id) {
                $data = $data->delete();
                if ($data) {
                    return $this->respond(1, '删除成功')->setStatusCode(200);
                }
            } else {
                return $this->respond(0, '无权限修改')->setStatusCode(200);
            }
        }
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

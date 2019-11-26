<?php

namespace App\Http\Controllers\Qq;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\ArticalUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Models\Artical;
use App\Models\Image;
use App\Models\Picture;
use Auth;
use App\Models\QqComment;
use App\Transformers\ArticleTransformer;
use Illuminate\Http\Request;
use App\Models\QqArticle;
use App\Models\QqArticleGood;
use App\Models\QqUserBasic;
use App\Models\QqUser;
use Illuminate\Support\Facades\Validator;

class Article extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * 获取当前用户发布过的的所有文章       查
     */
    //    public function index()
    //    {
    //        $articleAboutInfo = QqArticle::where('user_id', $operating_user)->get();
    //        foreach ($articleAboutInfo as $keys => $value) {
    //            $currentArticleId = $value->id;
    //            $value->author;
    //            $value->QqArticleGood;
    //            $countGood[$currentArticleId] =  QqArticleGood::where('article_id', $currentArticleId)->count();
    //            foreach ($value->comment as $key => $val) {
    //                $val->author;
    //            }
    //        }
    //
    //        return response()->json([
    //            "articleAboutInfo" => $articleAboutInfo,
    //            "countGood" => $countGood,
    //            "messge" => "Get Successfully"
    //        ], 200);
    //    }

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
        $rules = [
            'content' => 'required|min:3'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
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
                //                $data['mini_path'] = $result['mini_path'];
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
    //    public function judge($id)
    //    {
    //        //找到操作对应的用户
    //        $operating_user = $this->user()->id;
    //        //根据约束条件文章id 获取文章
    //        $data = QqArticle::find($id);
    //
    //        if (is_null($data)) {
    //            return response()->json(["messg" => "Record not found"], 404);
    //        }
    //        //获取当前文章的发布者id  判断是否允许删除
    //        $cur_art_pub = $data->user_id;
    //        if ($operating_user == $cur_art_pub) {
    //            return true;
    //        } else {
    //            return false;
    //        }
    //    }
    protected function respond($code, $message, $data = null)
    {
        return $this->response->array([
            'code' => $code,
            'data' => $data,
            'message' => $message
        ]);
    }
}

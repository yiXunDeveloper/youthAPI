<?php

namespace App\Http\Controllers\Qq;

use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use App\Models\QqArticle;
use App\Models\QqComment;
use App\Models\QqUser;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(CommentRequest $request)
    {
        $data = $request->only(['content', 'article_id']);
        $data['user_id'] = $this->user()->id;
        if ($request->comment_id) {
            $data['comment_id'] = $request->comment_id;
        }
        $data = QqComment::create($data);

        return $this->respond(1, '评论成功', $data);
    }

    public function update(Request $request, $id)
    {
        //找到操作对应的用户
        $operating_user = $this->user()->id;
        //根据约束条件评论id 获取评论
        $data = QqComment::find($id);
        //获取当前评论的发布者id  判断是否允许修改
        $cur_com_pub = $data->user_id;

        if ($operating_user == $cur_com_pub) {
            $data->update($request->all());
            return response()->json($data, 200);
        } else {
            return response()->json(['errmessg' => 'Forbidden'], 403);
        }
    }

    public function destroy($id)
    {
        $data = QqComment::find($id);
        $author = QqArticle::where('article_id', $data->article_id)->first();
        if ($data) {
            if ($data->user_id == $this->user()->id || $author->user_id == $this->user()->id) {
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
            'message' => $message,
        ]);
    }
}

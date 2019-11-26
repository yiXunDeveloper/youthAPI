<?php

namespace App\Http\Controllers\Qq;

use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use App\Models\QqArticle;
use App\Models\QqComment;
use App\Models\QqUser;
use Illuminate\Support\Facades\Validator;

class Comment extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    protected function respond($code,$message,$data=null)
    {
        return $this->response->array([
            'code'=>$code,
            'data'=>$data,
            'message'=>$message
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * 发布评论  content user_id article_id三个字段必须 成功返回评论内容及id
     */
    public function store(CommentRequest $request)
    {
        $data = $request->only(['content','article_id']);
        $data['user_id'] = $this->user()->id;
        if($request->comment_id){
            $data['comment_id'] = $request->comment_id;
        }
        $data = QqComment::create($data);

        return $this->respond(1,'评论成功',$data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //找到操作对应的用户
        $operating_user = $this->user()->id;
        //根据约束条件评论id 获取评论
        $data = QqComment::find($id);
        if (is_null($data)) {
            return response()->json(["messg" => "Record not found"], 404);
        }
        //获取当前评论的发布者id  判断是否允许删除
        $cur_com_pub = $data->user_id;
        //获取当前文章的发布者id  判断是否允许删除
        $cur_art_pbu = $data->QqUser->id;
        if (($operating_user == $cur_com_pub) || ($operating_user == $cur_art_pbu)) {
            $data->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['errmessg' => 'Forbidden'], 403);
        }
    }
}

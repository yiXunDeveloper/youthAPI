<?php

namespace App\Http\Controllers\Qq;

use Illuminate\Http\Request;
use App\Http\Requests\Qq\FansRequest;
// use App\Http\Controllers\Controller;
use App\Models\QqFans;
use Illuminate\Support\Facades\Validator;

class Fans extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * 获取个人关注列表
         */
        //找到 id 对应的用户
        // $operating_user = $this->user()->id;
        // $data = QqFans::where('fans_id', $operating_user)
        //     ->get();

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FansRequest $request)
    {
        /**
         * user_id 为当前被关注者id   fan_id为当前操作者的id
         */

        $data = $request->only(['user_id']);
        $data['fans_id'] = $this->user()->id;

        $data = QqFans::create($data);

        return $this->response(1, '关注成功', $data);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //传过来的参数为当前文章作者id
        //找到操作对应的用户
        $operating_user = $this->user()->id;
        //检查fans表中有无信息
        $data = QqFans::where('user_id', $id)
            ->where('fans_id', $operating_user)
            ->first();
        $record_id = $data->id;
        if (!$data) {
            QqFans::find($record_id)->delete();
            return $this->respond(1, '取消关注成功')->setStatusCode(200);
        } else {
            return $this->respond(0, '未查询到关注信息')->setStatusCode(200);
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

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

class MessageBoardController extends Controller
{
    /**
     * @return messageBoard
     * 从数据库获取数据返回给留言板
     * @param  object $data 从数据库(message_board)获取的数据倒序排列(desc)(经过了分页处理每页12条数据)
     * @param  integer $data ->lastPage() 获取$data的lastPage属性(最大页数)
     *
     */
    public function get_MessageBoard()
    {
        $data = DB::table('message_board')->orderBy('id', 'desc')->paginate(12);
        return( json_encode($data) );
        return view('messageBoaard', ['data' => $data, 'lastPage' => $data->lastPage()]);

    }

    /**
     * param: Request $request 获取从留言界面获取的数据
     * param: $result 判断添加是否成功
     * param: $time 获取当前时间格式(2019-06-08 15:35:18)
     * return
     */

    public function insert_MessageBoard(Request $request)
    {
        $time = date('Y-m-d H:i:s', time());
        //判断是否存在$request->receiver//留言接收者 没有赋值为NULL
        if (!isset($request->receiver)) {
            $request->receiver = 'NULL';
        }
        //判断是否添加数据成功 成功返回 留言页面 否则 返回 error
        $result = DB::insert('insert into message_board (author, message ,receiver,created_at ,updated_at) values (?,?, ?,?,?)', [$request->author, $request->message, $request->receiver, $time, $time]);

        if ($result == true) {
            $data = DB::table('message_board')->orderBy('id', 'desc')->paginate(12);
            return redirect("https://youthlab.sdut.edu.cn/app/msgboard/");
        } else {
            return 'error';
        }
    }

    function delete_MessageBoard($id, $key)
    {
        if ($key = 'Youth_Message_Board_Delete') {

            $id = (int)$id;
            if (gettype($id == 'integer') && $id > 0) {
                $result = DB::delete('delete from message_board where id = ?', [$id]);
                return redirect("https://youthlab.sdut.edu.cn/app/msgboard/");
                return gettype($id) . ':' . $id . $result;

            } else {
                return 'warning:非法指令!!';
            }
        } else {
            return "非法操作";
        }

    }

}

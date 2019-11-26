<?php

namespace App\Http\Controllers\Api;

use Alert;
use http\Env\Response;
use Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mockery\Matcher\Pattern;
use Psy\Command\DumpCommand;
use App\Http\Controllers\Input;
use function PHPSTORM_META\type;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LostAndFoundController extends Controller
{

    //web.php  里有一条测测试路由   测试页面  action 要手动修改




    public $tablelost = 'lf_lost';    //数据库表名
    public $tablefound = 'lf_found';   //数据库表名

    /**
     * 返回数据
     * 接收 $method
     * 判断 $method
     * 返回 所有数据 分页 +
     */
    public function gainFinderOrTheOwnerReleaseInfor(request $Request, $method)
    {
        //验证
        if (!$this->check($Request['id'], $Request['key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        };

        //执行
        if ($method == 1) {
            $data = DB::table($this->tablelost)->orderBy('id', 'desc')->paginate(6);
        } else
            if ($method == 2) {
                $data = DB::table($this->tablefound)->orderBy('id', 'desc')->paginate(6);
            }

        //返回数据
        return response()->json([$data], 200);
    }

    /**
     * 更新状态码
     */
    public function updateReleaseStatus($method, request $Request)
    {

        //验证
        if (!$this->check($Request['id'], $Request['key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        };

        //执行
        $pwd = $Request->input('pwd');
        $id = $Request->input('id');


        if ($method == 1) {
            $verify = DB::table($this->tablelost)
                ->select(DB::raw('lost_verify'))
                ->where('id', '=', $id)
                ->get();
        } else {
            $verify = DB::table($this->tablefound)
                ->select(DB::raw('found_verify'))
                ->where('id', '=', $id)
                ->get();
        }

        if (isset($verify[0]->lost_verify)) {
            $verify = $verify[0]->lost_verify;
        } else {
            $verify = $verify[0]->found_verify;
        }
        if ($pwd != $verify) {
            return response()->json(
                ['error' => "身份验证未通过"], 200
            );
        }


        if ($method == 1) {
            $res = DB::table($this->tablelost)
                ->where('id', $id)
                ->update(['lost_status' => 1]);
        } else {
            $res = DB::table($this->tablefound)
                ->where('id', $id)
                ->update(['found_status' => 1]);
        }
        if ($res) {
            return response()->json([
                'status' => '修改成功'
            ], 201);
        } else {
            return response()->json([
                'error' => '操作失败'
            ], 200);
        }


    }


    /**
     * 发布 寻物 找主 信息
     */
    public function finderOrTheOwnerRelease(Request $Request, $method)
    {
        //验证
        if (!$this->check($Request['id'], $Request['key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        };

        //执行
        $manName = $Request->input('manName');
        $verify = $Request->input('verify');
        $phoneNumber = $Request->input('phoneNumber');
        $someThing = $Request->input('someThing');
        $time = $Request->input('time');
        $place = $Request->input('place');
        $holder = $Request->input('holder');
        $detail = $Request->input('detail');
        $status = $Request->input('status');
        if ($place == null) //数据库中place字段不能为null
        {
            return response()->json([
                'warning' => '请完善信息'
            ], 412);
        }

        $pattern = "/^1[34578]\d{9}$/"; // "^"符号表示必须是1开头; "[ ]"的意思是第二个数字必须是中括号中一个数字; 而 \d 则表示0-9任意数字,后跟{9}表示长度是9个数字; 后面的$表示结尾;
        $res = preg_match($pattern, $phoneNumber);
        if (!$res)//使用正则表达式验证是否为手机号
        {
            return response()->json([
                'warning' => '请输入正确手机号码'
            ], 412);
        }
        /**$error=$_FILES['thingImg']['error'];//上传后系统返回的值
         * 1,上传的文件超过了 php.ini 中 upload_max_filesize选项限制的值。
         * 2,上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
         * 3,文件只有部分被上传。
         * 4,没有文件被上传。
         * 6，找不到临时文件夹。
         * 7,文件写入失败。
         */
        switch ($_FILES['thingImg']['error']) {
            case 0:
                break;
            case 1:
            case 2:
            case 3:
            case 4:
            case 6:
            case 7:
                return response()->json(['error' => '图片上传失败'], 200);
        }
        $upfile = $_FILES["thingImg"];
        $type = $upfile["type"]; //上传文件的类型
        $tmp_name = $upfile["tmp_name"]; //上传文件的临时存放路径
        //判断是否为图片
        $okType = false;
        switch ($type) {
            case 'image/pjpeg':
            case 'image/gif':
            case 'image/jpeg':
            case 'image/png':
                $okType = true;
                break;
        }
        if ($okType) {
            $nowTime = preg_replace('/\:/', '-', preg_replace('/\ /', '-', Carbon::now())); //将Carbon::now()函数返回的时间串中的空格替换为'-',':'替换为'-',注:图片名称的命名不能包含'\/:*<>|',否则会打不开文件;
            $returnType = trim(strrchr($type, '/'), '/'); //将type类型截取后几位格式字符;
            $storageImgName = $nowTime . "." . $returnType; //完善储存图片的名称 拼接字符小数点'.';
            if ($method == 1) {
                $result = move_uploaded_file($tmp_name, '../public/lostImg/' . $storageImgName);
                $thingImg = "../lostImg/" . $storageImgName;
            } else {
                $result = move_uploaded_file($tmp_name, '../public/foundImg/' . $storageImgName);
                $thingImg = "../founderImg/" . $storageImgName;
            }
        }
        if ($verify == "")//如果用户不填写验证字段，则默认为"000";
        {
            $verify = "000";
        }
        if ($method == 1) { //判断是寻宝还是寻主，选择DB插入语句，数据库中字段名不同。注:寻宝传值为1，寻主为2
            $insertResult = DB::table($this->tablelost)->insert(
                ['lost_man' => $manName, 'lost_verify' => $verify, 'lost_phone' => $phoneNumber, 'lost_thing' => $someThing, 'lost_time' => $time, 'lost_place' => $place, 'lost_detail' => $detail, 'lost_img' => $thingImg, 'lost_status' => $status, 'created_at' => date(now())]
            );
        } else {
            // dump($manName, $verify, $phoneNumber, $someThing, $time, $place, $holder, $detail, $thingImg, $status);
            $insertResult = DB::table($this->tablefound)->insert(
                ['found_man' => $manName, 'found_verify' => $verify, 'found_phone' => $phoneNumber, 'found_thing' => $someThing, 'found_time' => $time, 'found_place' => $place, 'found_holder' => $holder, 'found_detail' => $detail, 'found_img' => $thingImg, 'found_status' => $status, 'created_at' => date(now())]
            );
        }
        if ($insertResult) {
            return response()->json([
                'status' => '发布成功'
            ], 201);
        } else {
            return response()->json([
                'error' => '发布失败'
            ], 200);
        }
    }

    /**
     * 删除一条数据
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    //http://youthol.com/api/laf/deletedata/88/1?confirm=1

    public function deleteOneData(request $Request, $id, $method)
    {
        //验证
        if (!$this->check($Request['id'], $Request['key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        };

        if ($method == 1) {
            $deleteresults = DB::delete("delete from $this->tablelost where id = ?", [$id]);

        } else if ($method == 2) {
            $deleteresults = DB::delete("delete from $this->tablefound where id = ?", [$id]);

        } else {
            $deleteresults = 0;
        }


        if ($deleteresults) {
            return response()->json(['status' => '删除成功'], 201);
        }
        return response()->json(['error' => '删除失败'], 200);
    }

    /**
     * 操作权限验证
     * @param $id // 比对字段
     * @param $key // 验证字段
     * @return string
     */
    public function check($id, $key)
    {
        $signal = $key - (int)date('j') - (int)date("G"); //验证数据还原
        if ($id == $signal) {
            return true;
        } else
            if (
                (
                    $id - $signal == -1 //发送数据与接受数据跨越 一般整点
                    ||
                    $id - $signal == 23 //发送数据与接收数据跨越 零点
                )
                &&
                (
                    (int)date("i") < 35//分钟数小于一
                )
                &&
                (
                    (int)date("s") < 30 //秒数小于十
                )
            ) {
                return true;
            } else {
                return false;
            }

    }

    /**
     * 定时删除数据 正在测试
     */

    public function  deleteImg()
    {
        //定时删除图片
        //待开发
    }


// 以下为测试代码


    /**
     * 测试密码加密验证
     */
    public function founderReleaseView()
    {
        $verify = DB::table($this->tablelost)
            ->select(DB::raw('foundVerify'))
            ->where('id', '=', 11)
            ->get();
        $password = 123;
        $pwd = bcrypt($password);
        if (Hash::check(123, $pwd)) {
            echo "yes";
        } else {
            echo "no";
        }
        return view('founderReleaseView');
    }

    /**
     * 为指定用户显示详情
     * @param int $id
     * @return Response
     * @author LaravelAcademy.org
     */
    public function laf()
        //这是个无用函数
    {
        return 0;
    }


    public function test()
    {
//        $day = (int)date('j');// j 月份中的第几天，没有前导零 1 到 31
//        $hour = (int)date("G");// G 小时，24 小时格式，没有前导零 0 到 23
//        $minute = (int)date("i");//i 有前导零的分钟数 00 到 59>
//        $second = (int)date("s");//s 秒数，有前导零 00 到 59>
//
//
//        if ($this->check(12, 12 + (int)date('j') + 1)) {
//            echo "我成功了！！";
//        } else {
//            echo "我失败了";
//        }
//        $a = 1;
//        $b = 2;
//
//
//        for ($a = 0; $a < 10; $a++) {
//            if ($a == 2 || $a == 1) {
//            }
//        }


//
//        $disk = Storage::disk('local');
//        // 创建一个文件
//        $disk->put('file1.txt', 'Laravel Storage');
//
//        // 取到磁盘实例
//        $disk = Storage::disk('local');
//
//        // 取出文件
//        $file = $disk->get('app/file1.txt');
//
//        $size = Storage::size('app/file1.txt');
//        dump($file,$size);
// 取到磁盘实例

        $disk = Storage::disk('local');
        $directory = '/lostImg/';
        $lostImgDirection = '/lostImg/';
        $foundImgDirection = '/foundImg/';
        $i = 0;

        // 获取目录下的文件
        $files = $disk->files($directory);
        $lostImgs = $disk->files($lostImgDirection);
        $foundImgs = $disk->files($foundImgDirection);
        $max = count($lostImgDirection);

//        for($i = 0;$i < $max; $i ++ )
//        {
//            $name =  $files["$i"];
//            $name = explode('-'|| '/', $name);
//
//        }


        //  $max = count($lostImgDirection);


        echo count($files);
        $i = 0;
        $a = $files["$i"];// echo $a; 分隔符可以是斜线，点，或横线
        $dat = explode('-', $a);
        dump($dat);
        dump($files["$i"]);

        return 123;


    }
}

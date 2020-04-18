<?php

namespace App\Http\Controllers\Api;

use Alert;
use http\Env\Response;
use http\Message;
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
    public $tableimage = 'lf_img';   //数据库表名

// method == 1 lost
    /**
     * 返回数据列表
     * 接收 $method
     * 判断 $method
     * 返回 所有数据 分页 +
     */

    public function gainFinderOrTheOwnerReleaseInfor(request $Request, $method)
    {
        //验证
        if (!$this->check($Request['verify'], $Request['verify_key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        }

        //执行
        $dataImg = [];
        switch ($method) {
            case 2:
                {
                    $datas = DB::table($this->tablefound)->orderBy('id', 'desc')->paginate(6);
                    foreach ($datas as $data) {
                        $img = explode("|", $data->found_img);
                        foreach ($img as $data) {
                            $dataImg[] = $data;
                        }
                    }
                    $dataImgId = array_unique($dataImg);
                    $dataImgAll = DB::table($this->tableimage)->orderBy('id', 'desc')->wherein('id', $dataImgId)->get();
                    break;
                }
            default :
                {
                    $datas = DB::table($this->tablelost)->orderBy('id', 'desc')->paginate(6);

                    foreach ($datas as $data) {
                        $img = explode("|", $data->lost_img);
                        foreach ($img as $data) {
                            $dataImg[] = $data;
                        }
                    }
                    $dataImgId = array_unique($dataImg);
                    $dataImgAll = DB::table($this->tableimage)->orderBy('id', 'desc')->wherein('id', $dataImgId)->get();
//                    dd($dataImgAll);

                }
        }

        //返回数据
        return response()->json([$datas, 'imgs' => $dataImgAll], 200);
    }

    /**
     * 更新状态码
     */

    public function updateReleaseStatus($method, request $Request)
    {
        //验证
        if (!$this->check($Request['verify'], $Request['verify_key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        } else {
            // dd('success');
        };

        // dd((int)date('j') - (int)date("G"));


        $id = $Request->input('id');
        $status = $Request['status'];
        //执行
        $return_at = time();
        if ($method == 1) {
            $res = DB::table($this->tablelost)
                ->where('id', $id)
                ->update(['lost_status' => $status, 'found_at' => $return_at]);
        } else if ($method == 2) {
            $res = DB::table($this->tablefound)
                ->where('id', $id)
                ->update(['found_status' => $status, 'return_at' => $return_at]);
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

    // 修改数据
    public function updateData($method, request $Request)
    {
        //验证
        if (!$this->check($Request['verify'], $Request['verify_key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        } else {
            // dd('success');
        };


        $someThing = isset($Request['someThing']) ? $Request['someThing'] : null;
        $time = isset($Request['time']) ? $Request['time'] : null;
        $place = isset($Request['place']) ? $Request['place'] : null;
        $detail = isset($Request['detail']) ? $Request['detail'] : null;
        $status = isset($Request['status']) ? $Request['status'] : null;// 1信息已发布 2已经找回/领取
        $imgs = isset($Request['imgs']) ? $Request['imgs'] : null;// 字符串  | 分割各个id
        $imgsDel = isset($Request['imgsDel']) ? $Request['imgsDel'] : null;// 字符串  | 分割各个id
//echo ('imgs');
//var_dump($imgsDel);
//return ($Request->input());
//die();
        $thingImg = null;
        $hasData = false;
        // var_dump(strlen($someThing), strlen($time), strlen($place), strlen($detail), (integer)$status);
        if (strlen($someThing) >= 0 || strlen($time) > 0 || strlen($place) > 0 || strlen($detail) > 0 || (integer)$status == (1 || 2)) {
            $hasData = true;
        }
//        var_dump($hasData);

        if (!isset($Request['id'])) {
            return response()->json([
                'error' => '字段缺失'
            ], 200);
        }
        $id = $Request->input('id');
        if ($method == 1 && $hasData) {
            if ($someThing) {
                $updateData['lost_name'] = $someThing;
            }
            if ($time) {
                $updateData['lost_time'] = $time;
            }
            if ($place) {
                $updateData['lost_place'] = $place;

            }

            if ($detail) {
                $updateData['lost_detail'] = $detail;

            }
            if ($status) {
                $updateData['lost_status'] = $status;
            }

            if($imgs)
            {
                $updateData['lost_img'] = $imgs;
            }

//            var_dump('if');
        } else
            if ($method == 2 && $hasData) {

                if ($someThing) {
                    $updateData['found_name'] = $someThing;
                }
                if ($time) {
                    $updateData['found_time'] = $time;
                }
                if ($place) {
                    $updateData['found_place'] = $place;

                }
                if ($detail) {
                    $updateData['found_detail'] = $detail;
                }
                if ($status) {
                    $updateData['found_status'] = $status;
                }

                if($imgs)
                {
                    $updateData['found_img'] = $imgs;
                }

            } else {
                return response()->json([
                    'error' => '无效数据'
                ], 200);
            }

//        $updateData.length;
        if (!isset($updateData) || !sizeof($updateData)) {
            return response()->json([
                'error' => '无效数据'
            ], 200);
        }
        //执行7
//        $return_at = time();
        $updateData['updated_at'] = date(now());
//        DB::beginTransaction(); //开启事务
//
//        $imgStatus = true;
//        $imgDelStatus = true;
//        $imgAddStatus = true;
//        var_dump($imgsDel);
//        if ($imgsDel) {
//            $imgId = explode('|', $imgsDel);
//            $imgId[0] = isset($imgId[0]) ? $imgId[0] : 0;
//            $imgId[1] = isset($imgId[1]) ? $imgId[1] : 0;
//            $imgId[2] = isset($imgId[2]) ? $imgId[2] : 0;
//            $imgDelStatus = DB::update('update lf_img set is_used = 0 where id in (?,?,?)', $imgId);
//            echo ('imgdel:'.$imgDelStatus);
//        }
//        if ($imgs) {
//            $imgId = explode('|', $imgs);
//            $imgId[0] = isset($imgId[0]) ? $imgId[0] : 0;
//            $imgId[1] = isset($imgId[1]) ? $imgId[1] : 0;
//            $imgId[2] = isset($imgId[2]) ? $imgId[2] : 0;
//            $imgAddStatus = DB::update('update lf_img set is_used = 1 where id in (?,?,?)', $imgId);
//        }

        if ($method == 1) {
            $res = DB::table($this->tablelost)->where('id', $id)->update($updateData);
        } else if ($method == 2) {
            $res = DB::table($this->tablefound)->where('id', $id)->update($updateData);
        }
        if ($res) {
            return response()->json([
                'status' => '修改成功'
            ], 201);
        } else {
            return response()->json([
                'error' => '修改失败'
            ], 200);
        }
    }

    /**
     * 发布 寻物 找主 信息
     */
    public function finderOrTheOwnerRelease(Request $Request, $method)
    {

//        // dd($Request->file());
//        // dd($Request->input());
        //验证
//        if (!$this->check($Request['id'], $Request['key'])) {
//            return response()->json([
//                'error' => '非法访问'
//            ], 200);
//        };
        //执行
        // 获取数据
        $thingName = $Request->input('thingName');
        $time = $Request->input('time');
        $place = $Request->input('place');
        $detail = $Request->input('detail');
        $personName = $Request->input('personName');
//        $verify = $Request->input('verify');
        $phoneNumber = $Request->input('phoneNumber');
        $status = 1;// 1信息已发布 2已经找回/领取
        $thingImg = isset($Request['imgs']) ? $Request['imgs'] : null;// 字符串  | 分割各个id
//        $holder = $Request->input('holder') ;

        //数据验证
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

        DB::beginTransaction(); //开启事务
        if ($method == 1) { //判断是寻宝还是寻主，选择DB插入语句，数据库中字段名不同。注:寻宝传值为1，寻主为2
            $insertResult = DB::table($this->tablelost)->insert(
                [
                    'lost_name' => (string)$thingName,
                    'lost_time' => $time,
                    'lost_place' => $place,
                    'lost_detail' => $detail,
                    'lost_img' => $thingImg,
                    'lost_person' => $personName,
                    'lost_phone' => $phoneNumber,
                    'lost_status' => $status,
                    'created_at' => date(now()),

                ]
            );

        } else if ($method == 2) {

            $insertResult = DB::table($this->tablefound)->insert(
                [
                    'found_name' => $thingName,
                    'found_time' => $time,
                    'found_place' => $place,
                    'found_detail' => $detail,
                    'found_img' => $thingImg,
                    'found_person' => $personName,
                    'found_phone' => $phoneNumber,
                    'found_status' => $status,
                    'created_at' => date(now())
                ]);
        }

        if ($thingImg) {
            $imgId = explode('|', $thingImg);
            $imgId[0] = isset($imgId[0]) ? $imgId[0] : 0;
            $imgId[1] = isset($imgId[1]) ? $imgId[1] : 0;
            $imgId[2] = isset($imgId[2]) ? $imgId[2] : 0;
            $imgStatus = DB::update('update lf_img set is_used = 1 where id in (?,?,?)', $imgId);
        }

        if ($insertResult && $imgStatus) { //判断两条同时执行成功
            DB::commit(); //提交
            return response()->json([
                'status' => '发布成功'
            ], 201);
        } else {

            DB::rollback();
            //回滚
            return response()->json([
                'error' => '发布失败'
            ], 200);
        }


    }


    /**
     * 删除一条数据
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOneData(request $Request, $id, $method)
    {
        //验证
        if (!$this->check($Request['verify'], $Request['verify_key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        } else {
            // dd('success');
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

    // 搜索信息
    public function getDataBy(Request $Request, $method = 1)
    {
        if (!$this->check($Request['verify'], $Request['verify_key'])) {
            return response()->json([
                'error' => '非法访问'
            ], 200);
        }
        $imgId = [];
        $imgId[0] = isset($imgId[0]) ? $imgId[0] : 0;
        $imgId[1] = isset($imgId[1]) ? $imgId[1] : 0;
        $imgId[2] = isset($imgId[2]) ? $imgId[2] : 0;
        $imgStatus = DB::update('update lf_img set is_used = 0 where id in (?,?,?)', $imgId);

        die();
        $keyWord = $Request['keyWord'];
        $startTime = isset($Request['startTime']) ? $Request['startTime'] : 0;
        $endTime = isset($Request['endTime']) ? $Request['endTime'] : time();
        $status = $Request['status'] ? $Request['status'] : 1;
        $searchKey = isset($Request['searchKey']) ? $Request['searchKey'] : 1;
//var_dump($searchKey);
        switch (isset($searchKey) ? $searchKey : 1) {
            case 1:
                $searchKey = 'name';
                break;
            case 2:
                $searchKey = 'place';
                break;
            case 3:
                $searchKey = 'detail';
                break;
            default :
                $searchKey = 'name';
        }
//        dd($searchKey);
//        dd($searchKey);
//        if (isset($Request['searchKey'])) {
//
//            if ($Request['searchKey'] == 1) {
//
//                $searchKey = 'name';
//            } else
//                if ($Request['searchKey'] == 2) {
//                    $searchKey = 'place';
//                } else if($Request['searchKey']== 3){
//                    $searchKey = 'detail';
//                }
//                else
//                {
//                    $searchKey = 'name';
//                }
//        } else {
//            $searchKey = 'name';
//        }


        if ($method == 1) {
            // var_dump('method = 1');

            $data = DB::table($this->tablelost)->whereBetween('lost_time', array((integer)$startTime, (integer)$endTime))->where("lost_{$searchKey}", 'like', "%$keyWord%")->where('lost_status', $status)->paginate(6);
            // dd($data, $status);
        } else
            if ($method == 2) {
                // var_dump('method = 2');

                $data = DB::table($this->tablefound)->whereBetween('found_time', array((integer)$startTime, (integer)$endTime))->where("found_{$searchKey}", 'like', "%$keyWord%")->where('found_status', $status)->paginate(6);
                // dd($data);
            } else {
                return response()->json(['error' => '查询失败'], 201);
            }
        if ($data) {
            return response()->json([$data], 200);
        }
    }


    /**
     * 操作权限验证
     *
     * @param $id  // 比对字段
     * @param $key // 验证字段
     * @return string
     */
    public function check($id, $key)
    {
        $signal = $key - (int)date('j') - (int)date("G"); //验证数据还原
        //      j - 一个月中的第几天，不带前导零（1 到 31）
        //      G - 24 小时制，不带前导零（0 到 23）

        if ($id == $signal) {
            return true;
        } else
            if (($id - $signal == -1 /*发送数据与接受数据跨越 一般整点*/ ||
                    $id - $signal == 23 /*发送数据与接收数据跨越 零点*/
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
    public function deleteImg()
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
     *
     * @param int $id
     * @return Response
     * @author LaravelAcademy.org
     */
    //这是个无用函数


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

    public function uploadImg(Request $request)
    {
        /**$error=$_FILES['thingImg']['error'];//上传后系统返回的值
         * 1,上传的文件超过了 php.ini 中 upload_max_filesize选项限制的值。
         * 2,上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
         * 3,文件只有部分被上传。
         * 4,没有文件被上传。
         * 6，找不到临时文件夹。
         * 7,文件写入失败。
         */

        $strs = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm";
        $randString = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 5);
//        var_dump($randString);
        if (isset($_FILES['fileImage'])) {
//dd($_FILES);

            switch ($_FILES['fileImage']['error']) {
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
            $upfile = $_FILES["fileImage"];
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
                $nowTime = preg_replace('/\:/', '', preg_replace('/\ /', '', preg_replace('/\-/', '', Carbon::now()))); //将Carbon::now()函数返回的时间串中的空格替换为'-',':'替换为'-',注:图片名称的命名不能包含'\/:*<>|',否则会打不开文件;
                $returnType = trim(strrchr($type, '/'), '/'); //将type类型截取后几位格式字符;
                $storageImgName = $randString . $nowTime . "." . $returnType; //完善储存图片的名称 拼接字符小数点'.';
                $result = move_uploaded_file($tmp_name, '../public/lafImg/' . $storageImgName);
//                return $storageImgName;
                $thingImg = "/lafImg/" . $storageImgName;
                if ($result) {
                    $insertResult = DB::table($this->tableimage)->insert(
                        [
                            'img_name' => $thingImg,
                            'is_used' => 0,
                            'created_at' => date(now()),
                        ]
                    );
                    if (!$insertResult) {
                        return response()->json([
                            'status' => 'error',
                            'msg' => '图片保存失败',
                        ], 201);
                    } else {
                        $imgData = DB::table($this->tableimage)->where("img_name", '=', $thingImg)->where('is_used', 0)->get();
                        return response()->json([
                            'status' => 'OK',
                            'msg' => '图片上传成功',
                            'data' => $imgData,
                        ], 201);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'msg' => '图片上传失败',
                    ], 201);
                }

            }
        }

    }

    public function getImages($imgName)
    {
//        dd(123132);
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm";
        $name = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 5);
        echo $name;
        die();
        return asset('/foundImg/' . $imgName);
    }
}

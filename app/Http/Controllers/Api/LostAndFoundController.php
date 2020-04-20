<?php

namespace App\Http\Controllers\Api;

use Alert;
use http\Env\Response;
use http\Message;
use Illuminate\Foundation\Console\Presets\React;
use mysql_xdevapi\Exception;
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
    private $tablelost = 'lf_lost';
    private $tablefound = 'lf_found';
    private $tableimage = 'lf_img';
    private $tableadmin = 'lf_admin';

    /**
     * 发布 寻物 找主 信息
     *
     * @param Request $Request
     * @param         $method
     * @return \Illuminate\Http\JsonResponse
     */
    public function finderOrTheOwnerRelease(Request $Request, $method)
    {
        $thingName = $Request->input('thingName');
        $time = $Request->input('time');
        $place = $Request->input('place');
        $detail = $Request->input('detail');
        $personName = $Request->input('personName');
        $phoneNumber = $Request->input('phoneNumber');
        $status = 1;
        $thingImg = isset($Request['imgs']) ? $Request['imgs'] : 0;
        if ($place == null) {
            return response()->json([
                'status' => 'warning',
                'msg' => '请完善信息'
            ], 412);
        }
        $pattern = "/^1[34578]\d{9}$/";
        $res = preg_match($pattern, $phoneNumber);
        if (!$res) {
            return response()->json([
                'status' => 'warning',
                'msg' => '请输入正确手机号码'
            ], 412);
        }
        if ($method == 1) {
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
        if ($insertResult) {
            return response()->json([
                'status' => 'ok',
                'msg' => '发布成功'
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => '发布失败'
            ], 200);
        }
    }

    /**
     * 删除一条数据
     *
     * @param Request $Request
     * @param         $id
     * @param         $method
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOneData(request $Request, $id, $method)
    {
        if (!$this->checkAdmin($Request->input('randKey'), $Request->input('uName'), true)) {
            return response()->json([
                'status' => 'warning',
                'msg' => '权限不足'
            ], 200);
        }
        if ($method == 1) {
            $deleteresults = DB::delete("delete from $this->tablelost where id = ?", [$id]);
        } else if ($method == 2) {
            $deleteresults = DB::delete("delete from $this->tablefound where id = ?", [$id]);
        } else {
            $deleteresults = 0;
        }
        if ($deleteresults) {
            return response()->json([
                'status' => 'ok',
                'msg' => '删除成功'
            ], 201);
        }
        return response()->json([
            'status' => 'error',
            'msg' => '删除失败'
        ], 200);
    }

    /**
     * 更新状态码
     *
     * @param         $method
     * @param Request $Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReleaseStatus($method, request $Request)
    {
        if (!isset($Request['checkUser']) || !isset($Request['checkPhone'])) {
            return response()->json([
                'status' => 'error',
                'msg' => '无效验证数据'
            ], 200);
        }
        $id = $Request->input('id');
        $status = $Request['status'];
        $return_at = date(now());
        if ($method == 1) {
            $check = DB::table($this->tablelost)->where('id', $id)->first();
            if ($check && $Request['checkUser'] == $check->lost_person && $Request['checkPhone'] == $check->lost_phone) {
                $res = DB::table($this->tablelost)
                    ->where('id', $id)
                    ->update(['lost_status' => $status, 'found_at' => $return_at]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg' => '身份验证未通过'
                ]);
            }
        } else if ($method == 2) {
            $check = DB::table($this->tablefound)->where('id', $id)->first();
            if ($check && $Request['checkUser'] == $check->found_person && $Request['checkPhone'] == $check->found_phone) {
                $res = DB::table($this->tablefound)->where('id', $id)->update(['found_status' => $status, 'return_at' => $return_at]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg' => '身份验证未通过'
                ]);
            }
        }
        if ($res) {
            return response()->json([
                'status' => 'ok',
                'msg' => '修改成功'
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => '操作失败'
            ], 200);
        }
    }

    /**
     * 更新数据
     *
     * @param         $method
     * @param Request $Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateData($method, request $Request)
    {
        if (!$this->checkAdmin($Request->input('randKey'), $Request->input('uName'), true)) {
            return response()->json([
                'status' => 'warning',
                'msg' => '权限不足'
            ], 200);
        }
        $someThing = isset($Request['someThing']) ? $Request['someThing'] : null;
        $time = isset($Request['time']) ? $Request['time'] : null;
        $place = isset($Request['place']) ? $Request['place'] : null;
        $detail = isset($Request['detail']) ? $Request['detail'] : null;
        $status = isset($Request['status']) ? $Request['status'] : null;
        $imgs = isset($Request['imgs']) ? $Request['imgs'] : null;
        $thingImg = null;
        $hasData = false;
        if (strlen($someThing) >= 0 || strlen($time) > 0 || strlen($place) > 0 || strlen($detail) > 0 || (integer)$status == (1 || 2)) {
            $hasData = true;
        }
        if (!isset($Request['id'])) {
            return response()->json([
                'status' => 'warning',
                'msg' => '字段缺失'
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
            if ($imgs) {
                $updateData['lost_img'] = $imgs;
            }
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
                if ($imgs) {
                    $updateData['found_img'] = $imgs;
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg' => '无效数据'
                ], 200);
            }
        if (!isset($updateData) || !sizeof($updateData)) {
            return response()->json([
                'status' => 'error',
                'msg' => '无效数据'
            ], 200);
        }
        $updateData['updated_at'] = date(now());
        if ($method == 1) {
            $res = DB::table($this->tablelost)->where('id', $id)->update($updateData);
        } else if ($method == 2) {
            $res = DB::table($this->tablefound)->where('id', $id)->update($updateData);
        }
        if ($res) {
            return response()->json([
                'status' => 'ok',
                'msg' => '修改成功'
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => '修改失败'
            ], 200);
        }
    }

    /**
     * 分页返回数据列表
     *
     * @param Request $Request
     * @param         $method
     * @return \Illuminate\Http\JsonResponse
     */
    public function gainFinderOrTheOwnerReleaseInfor(request $Request, $method)
    {
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
                }
        }
        return response()->json([$datas, 'imgs' => $dataImgAll], 200);
    }

    /**
     * 查询
     *
     * @param Request $Request
     * @param int     $method
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDataBy(Request $Request, $method = 1)
    {
<<<<<<< HEAD
=======

>>>>>>> merge
        $imgId = [];
        $imgId[0] = isset($imgId[0]) ? $imgId[0] : 0;
        $imgId[1] = isset($imgId[1]) ? $imgId[1] : 0;
        $imgId[2] = isset($imgId[2]) ? $imgId[2] : 0;
        $imgStatus = DB::update('update lf_img set is_used = 0 where id in (?,?,?)', $imgId);
        $keyWord = $Request['keyWord'];
        $startTime = isset($Request['startTime']) ? $Request['startTime'] : 0;
        $endTime = isset($Request['endTime']) ? $Request['endTime'] : time();
        $status = $Request['status'] ? $Request['status'] : 1;
        $searchKey = isset($Request['searchKey']) ? $Request['searchKey'] : 1;
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
        if ($method == 1) {
            $datas = DB::table($this->tablelost)->whereBetween('lost_time', array((integer)$startTime, (integer)$endTime))->where("lost_{$searchKey}", 'like', "%$keyWord%")->where('lost_status', $status)->paginate(6);
            foreach ($datas as $data) {
                $img = explode("|", $data->lost_img);
                foreach ($img as $data) {
                    $dataImg[] = $data;
                }
            }
            $dataImgId = array_unique($dataImg);
            $dataImgAll = DB::table($this->tableimage)->orderBy('id', 'desc')->wherein('id', $dataImgId)->get();
        } else
            if ($method == 2) {
                $datas = DB::table($this->tablefound)->whereBetween('found_time', array((integer)$startTime, (integer)$endTime))->where("found_{$searchKey}", 'like', "%$keyWord%")->where('found_status', $status)->paginate(6);
                foreach ($datas as $data) {
                    $img = explode("|", $data->found_img);
                    foreach ($img as $data) {
                        $dataImg[] = $data;
                    }
                }
                $dataImgId = array_unique($dataImg);
                $dataImgAll = DB::table($this->tableimage)->orderBy('id', 'desc')->wherein('id', $dataImgId)->get();
            } else {
                return response()->json(['error' => '查询失败'], 201);
            }
        if ($data) {
            return response()->json([$datas, 'imgs' => $dataImgAll], 200);
        }
    }

    /**
     * 上传图片
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImg(Request $Request)
    {
<<<<<<< HEAD
=======

>>>>>>> merge
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm";
        $randString = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 5);
        if (isset($_FILES['fileImage'])) {
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
            $type = $upfile["type"];
            $tmp_name = $upfile["tmp_name"];
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
                $nowTime = preg_replace('/\:/', '', preg_replace('/\ /', '', preg_replace('/\-/', '', Carbon::now())));
                $returnType = trim(strrchr($type, '/'), '/');
                $storageImgName = $randString . $nowTime . "." . $returnType;
                $result = move_uploaded_file($tmp_name, '../public/lafImg/' . $storageImgName);
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
                        ]);
                    } else {
                        $imgData = DB::table($this->tableimage)->where("img_name", '=', $thingImg)->where('is_used', 0)->get();
                        return response()->json([
                            'status' => 'OK',
                            'msg' => '图片上传成功',
                            'data' => $imgData,
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'msg' => '图片上传失败',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg' => '图片格式不符',
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => '图片上传失败',
            ]);
        }
    }

    /**
     * 验证管理员
     *
     * @param null $key
     * @param null $name
     * @param bool $return
     * @return \Illuminate\Http\JsonResponse|int
     */
    private function checkAdmin($key = null, $name = null, $return = false)
    {
        if (strlen($key) != 24 || strlen($name) < 5 || $key == NULL || $name == null || $name > 9) {
            if ($return) {
                return 0;
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg' => '非法访问'
                ]);
            }
        }
        $realKey = substr($key, 7, 8);
        try {
            $res = DB::table($this->tableadmin)->where('name', $name)->where('login_key', $realKey)->first();
        } catch (Exception $e) {
            if ($return) {
                return 0;
            }
            return response()->json([
                'status' => 'error',
                'msg' => '非法访问'
            ]);
        }
        {
            if ($return) {
                return ($res || isset($res->power)) ? $res->power : 0;
            }
            if ($res && !$res->power > 4) {
                return response()->json([
                    'status' => 'error',
                    'msg' => '非法访问'
                ]);
            }
        }
    }

    /**
     * 删除图片
     *
     * @param         $img
     * @param Request $Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delImg($img, Request $Request)
    {
        if (!$this->checkAdmin($Request->input('randKey'), $Request->input('uName'), true)) {
            return response()->json([
                'status' => 'warning',
                'msg' => '权限不足'
            ], 200);
        }
        $imgdir = public_path() . '/lafImg/';
        $imgdir = str_replace('\\', '/', $imgdir);
        $file = $imgdir . $img;
        if (is_readable($file)) {
            if (!unlink($file)) {
                return response()->json([
                    'status' => 'error',
                    'msg' => '删除失败'
                ]);
            } else {
                return response()->json([
                    'status' => 'ok',
                    'msg' => '删除成功'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => '读取失败'
            ]);
        }
    }

    /**
     * 管理员登陆
     *
     * @param Request $Request
     * @param null    $login_key
     * @return \Illuminate\Http\JsonResponse|int
     */
    public function login(Request $Request, $login_key = null)
    {
        if (!isset($Request['name']) || (!isset($Request['pass']))) {
            return 0;
        }
        $name = $Request['name'];
        $pass = $Request['pass'];
        $isAdmin = DB::table($this->tableadmin)->where("name", [$name])->where("password", $pass)->first();
        if (!$isAdmin) {
            return response()->json([
                'status' => 'error',
                'msg' => '登陆失败',
            ], 201);
        }
        $id = $isAdmin->id;
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm1234567890";
        $randString = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 8);
        $fakeStringB = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 7);
        $fakeStringE = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 9);
        $res = DB::table($this->tableadmin)->where('id', $id)->update(['login_key' => $randString, 'login_at' => date(now())]);
        if ($res) {
            return response()->json([
                'status' => 'ok',
                'msg' => '登录成功',
                'key' => $fakeStringB . $randString . $fakeStringE
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => '登陆失败',
            ], 200);
        }
    }
}

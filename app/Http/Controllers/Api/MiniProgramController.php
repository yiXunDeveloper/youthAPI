<?php

namespace App\Http\Controllers\Api;

use App\Models\MiniProgramDepartment;
use App\Models\MiniRecruitNotice;
use Dingo\Api\Http\Request;

use  GuzzleHttp\choose_handler;
use QL\QueryList;
use GuzzleHttp\Client;

class MiniProgramController extends Controller
{
    /**
     * 2019年纳新，获取各部门简介
     */
    public function getDepartmentIntro()
    {
        $data = MiniProgramDepartment::all();
        if ($data) {
            return $this->response->array([
                'data' => $data
            ])->setStatusCode(200);
        } else {
            return $this->response->errorNotFound('没有找到该部门');
        }

    }

    public function recruitNotice(Request $request)
    {
        $notice = MiniRecruitNotice::all();
        if (count($notice)) {
            if ($notice[0]->content) {
                return $this->response->array([
                    'code' => 1,
                    'msg' => $notice[0]->content,
                    'open' => $notice[0]->open
                ])
                    ->setStatusCode(200);
            }
        }

        return $this->response->array([
            'code' => 0,
            'msg' => '无',
        ])
            ->setStatusCode(200);

    }
    /**
     * 2019新型冠状病毒数据接口
     */
    public function getData2019nCoV()
    {
        $need = null;
        $data = QueryList::get('https://ncov.dxy.cn/ncovh5/view/pneumonia')
            ->rules([
                'data' => array('script', 'text')
            ])
            ->queryData();
        foreach ($data as $value) {
            if (strpos($value['data'], "getAreaStat")) {
                $need = substr($value['data'], 27, strlen($value['data']) - 38);
                $phpdata = json_decode($need);
            }
        }
        return $need;
    }
}

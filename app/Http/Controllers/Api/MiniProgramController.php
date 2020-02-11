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


    public function getCountryData2019nCoV()
    {
        $countryData = null;

        $data = QueryList::get('https://ncov.dxy.cn/ncovh5/view/pneumonia')
            ->rules([
                'data' => array('script', 'text')
            ])
            ->queryData();

        foreach ($data as $value) {
            if (strpos($value['data'], "getStatisticsService")) {
                $countryData = substr($value['data'], 35, strlen($value['data']) - 46);
            }
        }

        if ($countryData == null) {
            return $this->response->array([
                "status" => 0,
                "error" => "未查询到数据"
            ]);
        }

        $countryData = json_decode($countryData);
        return $this->response->array([
            "status" => 1,
            "data" => [
                "confirmedCount" => $countryData->confirmedCount,
                "suspectedCount" => $countryData->suspectedCount,
                "curedCount" => $countryData->curedCount,
                "deadCount" => $countryData->deadCount,
                "seriousCount" => $countryData->seriousCount,
                "confirmedIncr" => $countryData->confirmedIncr,
                "suspectedIncr" => $countryData->suspectedIncr,
                "curedIncr" => $countryData->curedIncr,
                "deadIncr" => $countryData->deadIncr,
                "seriousIncr" => $countryData->seriousIncr
            ]
        ]);
    }

    public function getAllProvinceData2019nCoV()
    {
        $countryData = null;
        $data = QueryList::get('https://ncov.dxy.cn/ncovh5/view/pneumonia')
            ->rules([
                'data' => array('script', 'text')
            ])
            ->queryData();
        foreach ($data as $value) {
            if (strpos($value['data'], "getAreaStat")) {
                $countryData = substr($value['data'], 27, strlen($value['data']) - 38);
            }
        }
        return $this->response->array([
            'status' => 1,
            'data' => json_decode($countryData)
        ]);
    }

    /**
     * 2019新型冠状病毒数据接口 按省查找
     */
    public function getProvinceData2019nCoV($provinceName = null)
    {
        //在UTF-8编码下，一个汉字占3个字节
        if ($provinceName == null || strlen($provinceName) > 9) {
            return $this->response->array([
                'status' => 0,
                'error' => "请检查输入内容"
            ]);
        }

        $data = QueryList::get('https://ncov.dxy.cn/ncovh5/view/pneumonia')
            ->rules([
                'data' => array('script', 'text')
            ])
            ->queryData();
        foreach ($data as $value) {
            if (strpos($value['data'], "getAreaStat")) {
                $allProvinceData = substr($value['data'], 27, strlen($value['data']) - 38);
            }
        }

        $allProvinceData = json_decode($allProvinceData);
        foreach ($allProvinceData as $provinceData) {
            if ($provinceName == $provinceData->provinceShortName) {
                return $this->response->array([
                    'status' => 1,
                    'data' => $provinceData
                ]);
            }
        }
        return $this->response->array([
            'status' => 0,
            'error' => "未查询到数据,请检查输入内容"
        ]);
    }
}

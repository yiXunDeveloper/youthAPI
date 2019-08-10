<?php

namespace App\Http\Controllers\Api;

use App\Models\MiniProgramDepartment;

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
}

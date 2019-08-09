<?php

namespace App\Http\Controllers\Api;

use App\Models\MiniProgramDepartment;
use Illuminate\Http\Request;

class MiniProgramController extends Controller
{
    public function getDepartmentIntro(Request $request)
    {
        $id = $request->input('id');
        $data = MiniProgramDepartment::where('id', $id)->first();
        if ($data) {
            return $this->response->array([
                'id' => $data->id,
                'department' => $data->department,
                'intro' => $data->intro
            ])->setStatusCode(200);
        } else {
            return $this->response->errorNotFound('没有找到该部门');
        }

    }
}

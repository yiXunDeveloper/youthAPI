<?php

namespace App\Http\Controllers\Api\Recruit;

use App\Http\Requests\InfoRequest;
use App\Models\Recruit\BenPosition;
use App\Models\Recruit\Education;
use App\Models\Recruit\Honour;
use App\Models\Recruit\Image;
use App\Models\Recruit\Information;
use App\Models\Recruit\LearnExp;
use App\Models\Recruit\Work;
use App\Models\Recruit\YanPosition;
use App\Transformers\Recruit\InfoTransformer;
use Dingo\Api\Auth\Auth;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function store(InfoRequest $request)
    {

        if($request->info_type == 'information') {
            $user = \Auth::guard('recruit')->user();
            $info = $request->only('political_status', 'birthplace', 'now_work_place', 'highest_education', 'professional_code', 'graduated_time', 'sex', 'nation', 'marriage', 'file_unit', 'highest_degree', 'graduated_school', 'birth_year', 'birth_mouth', 'learn_subject', 'apply_position', 'is_graduates');
            $attributes = $request->only(['email', 'phone', 'id_nb', 'avatar_id', 'name']);
            $info['user_id'] = \Auth::guard('recruit')->user()->id;
            $user = $user->update($attributes);
            if($user->id == null) {
                $infos = Information::create($info);
            }else{
                $infos = Information::update($info);
            }
            return $this->response->item($user, new InfoTransformer());
        }elseif ($request->info_type == 'learn'){
            $learn = $request->only('join_time','graduate_time','graduate_school','major','education','bachelor','learn_way');
            $learn['user_id'] = \Auth::guard('recruit')->user()->id;
            $learn = LearnExp::create($learn);
            $learns['data'] = LearnExp::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($learns);
        }elseif ($request->info_type == 'work'){
            $work = $request->only('join_time','drop_time','company','position');
            $work['user_id'] = \Auth::guard('recruit')->user()->id;
            $work = Work::create($work);
            $works['data'] = Work::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($works);
        }elseif ($request->info_type == 'education'){
            $edu = $request->only('ben_code','xue_code','yan_code','shuo_code');
            $edu['user_id'] = \Auth::guard('recruit')->user()->id;
            $edu = Education::create($edu);
            $edus['data'] = Education::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($edus);
        }elseif ($request->info_type == 'ben_position') {
            $position = $request->only('begin_time','over_time','witness_name','witness_position','witness_phone','position');
            $position['user_id'] = \Auth::guard('recruit')->user()->id;
            $position = BenPosition::create($position);
            $positions['data'] = BenPosition::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($positions);
        }elseif ($request->info_type == 'yan_position'){
            $position = $request->only('begin_time','over_time','witness_name','witness_position','witness_phone','position');
            $position['user_id'] = \Auth::guard('recruit')->user()->id;
            $position = YanPosition::create($position);
            $positions['data'] = YanPosition::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($positions);
        }
        elseif ($request->info_type == 'honour'){
            $honour = $request->only('honour_name','get_time');
            $honour['user_id'] = \Auth::guard('recruit')->user()->id;
            $honour = Honour::create($honour);
            $honours['data'] = Honour::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($honours);
        }else{
            return $this->response->errorUnauthorized('There are something with wrong');
        }
    }
    public function update(InfoRequest $request,$type,$id)
    {
        if($request->info_type == 'learn'){
            $learn = LearnExp::find($id);
            $learn->join_time = $request->join_time;
            $learn->graduate_time = $request->graduate_time;
            $learn->graduate_school = $request->graduate_school;
            $learn->major = $request->major;
            $learn->education = $request->education;
            $learn->bachelor = $request->bachelor;
            $learn->learn_way = $request->learn_way;
            $learn->update();
            $learns['data'] = LearnExp::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return $learns;
        }elseif ($request->info_type == 'work'){
            $work = Work::find($id);
            $work->join_time = $request->join_time;
            $work->drop_time = $request->drop_time;
            $work->company = $request->company;
            $work->position = $request->position;
            $work->update();
            $works['data'] = Work::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return $works;
        }elseif ($request->info_type == 'education'){
            if($request->ben_code ==null && $request->xue_code == null && $request->yan_code == null && $request->shuo_code == null)
            {
                return $this->response->errorUnauthorized('请输入证书编号');
            }
            $edu = Education::find($id);
            $edu->ben_code = $request->ben_code;
            $edu->xue_code = $request->xue_code;
            $edu->yan_code = $request->yan_code;
            $edu->shuo_code = $request->shuo_code;
            $edu->update();
            $edus = Work::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return $edus;
        }elseif ($request->info_type == 'ben_position')
        {
            $position = BenPosition::find($id);
            $position->begin_time = $request->begin_time;
            $position->over_time = $request->over_time;
            $position->witness_name = $request->witness_name;
            $position->witness_position = $request->witness_position;
            $position->witness_phone = $request->witness_phone;
            $position->position = $request->position;
            $position->update();
            $positions['data'] = BenPosition::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($positions);
        }elseif ($request->info_type == 'yan_position'){
            $position = YanPosition::find($id);
            $position->begin_time = $request->begin_time;
            $position->over_time = $request->over_time;
            $position->witness_name = $request->witness_name;
            $position->witness_position = $request->witness_position;
            $position->witness_phone = $request->witness_phone;
            $position->position = $request->position;
            $position->update();
            $positions['data'] = YanPosition::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($positions);
        } elseif ($request->info_type == 'honour'){

            $honour = Honour::find($id);
            $honour->get_time = $request->get_time;
            $honour->honour_name = $request->honour_name;
            $honour->update();
            $honours['data'] = Work::where('user_id',\Auth::guard('recruit')->user()->id)->get();
            return json_encode($honours);
        }
    }
    public function delete(Request $request,$type,$id)
    {
        switch ($request->info_type){
            case 'learn':
                $learn = LearnExp::find($id);
                $learn->delete();
                return $this->response->noContent();
                break;
            case 'work':
                $work = Work::find($id);
                $work->delete();
                return $this->response->noContent();
                break;
            case 'education':
                $edu = Education::find($id);
                $edu->delete();
                return $this->response->noContent();
                break;
            case 'ben_position':
                $ben = BenPosition::find($id);
                $ben->delete();
                return $this->response->noContent();
                break;
            case 'yan_position':
                $yan = YanPosition::find($id);
                $yan->delete();
                return $this->response->noContent();
                break;
            case 'honour':
                $honour = Honour::find($id);
                $honour->delete();
                return $this->response->noContent();
                break;
        }
    }
    public function lookInformation(Request $request)
    {
        $user = \Auth::guard('recruit')->user();
        switch ($request->info_type){
            case 'information':
                $information = $user->information;
                return $this->response->item($information, new InfoTransformer());
                break;
            case 'learn':
                $learn['data'] = $user->learn;
                return json_encode($learn);
                break;
            case 'work':
                $work['data'] = $user->work;
                return json_encode($work);
                break;
            case 'education':
                $education['data'] = $user->education;
                return json_encode($education);
                break;
            case 'ben_position':
                $ben_position['data'] = $user->ben_position;
                return json_encode($ben_position);
                break;
            case 'yan_position':
                $yan_position['data'] = $user->yan_position;
                return json_encode($yan_position);
                break;
            case 'honour':
                $honour['data'] = $user->honour;
                return json_encode($honour);
                break;
        }
    }
}

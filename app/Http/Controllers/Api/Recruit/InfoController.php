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
        $user = \Auth::guard('recruit')->user();
        $info = $request->only('political_status', 'birthplace', 'now_work_place', 'highest_education', 'professional_code', 'graduated_time', 'sex', 'nation', 'marriage', 'file_unit', 'highest_degree', 'graduated_school', 'birth_year', 'birth_mouth', 'learn_subject', 'apply_position', 'is_graduates');
        $attributes = $request->only(['email', 'phone', 'id_nb', 'avatar_id', 'name']);
        $info['user_id'] = \Auth::guard('recruit')->user()->id;
        $user = $user->update($attributes);
        if($request->info_type == 'information') {
            $is = Information::all()->where('user_id',$info['user_id']);
            if(count($is)) {
                $infos = Information::find($is[0]->id);
                $infos = $infos->update($info);
                $infos = Information::find($is[0]->id);
            }else{
                $infos = Information::create($info);
            }
            return $this->response->item($infos, new InfoTransformer());
        }elseif ($request->info_type == 'learn'){
            $learn = $request->only('join_time','graduate_time','graduate_school','major','education','bachelor','learn_way');
            $learn['user_id'] = \Auth::guard('recruit')->user()->id;
            $learn = LearnExp::create($learn);
            return $this->lookInformation('learn');
        }elseif ($request->info_type == 'work'){
            $work = $request->only('join_time','drop_time','company','position');
            $work['user_id'] = \Auth::guard('recruit')->user()->id;
            $work = Work::create($work);
            return $this->lookInformation('work');
        }elseif ($request->info_type == 'education'){
            $edu = $request->only('ben_code','xue_code','yan_code','shuo_code');
            $edu['user_id'] = \Auth::guard('recruit')->user()->id;
            $edu = Education::create($edu);
            return $this->lookInformation('education');
        }elseif ($request->info_type == 'ben_position' || $request->info_type == 'yan_position') {
            $position = $request->only('begin_time','over_time','witness_name','witness_position','witness_phone','position');
            $position['user_id'] = \Auth::guard('recruit')->user()->id;
            if($request->info_type == 'ben_position'){
                $position = BenPosition::create($position);
                return $this->lookInformation('ben_position');
            }else{
                $position = YanPosition::create($position);
                return $this->lookInformation('yan_position');
            }
        }elseif ($request->info_type == 'honour'){
            $honour = $request->only('honour_name','get_time');
            $honour['user_id'] = \Auth::guard('recruit')->user()->id;
            $honour = Honour::create($honour);
            return $this->lookInformation('honour');
        }else{
            return $this->response->errorUnauthorized('没有此接口');
        }
    }
    public function update(Request $request,$type,$id)
    {
        if($request->info_type == 'learn'){
            $learn = $request->only('join_time','graduate_time','graduate_school','major','education','bachelor','learn_way');
            $learna = LearnExp::find($id);
            $learn->update($learn);
            return $this->lookInformation('learn');
        }elseif ($request->info_type == 'work'){
            $worka = $request->only('join_time','drop_time','company','position');
            $work = Work::find($id);
            $work->update($worka);
            return $this->lookInformation('work');
        }elseif ($request->info_type == 'education'){
            $edua = $request->only('ben_code','xue_code','yan_code','shuo_code');
            $edu = Education::find($id);
            $edu->update($edua);
            return $this->lookInformation('education');
        }elseif ($request->info_type == 'ben_position' || $request->info_type == 'yan_position')
        {
            if($request->info_type == 'ben_position'){
                $position = BenPosition::find($id);
            }else{
                $position = YanPosition::find($id);
            }
            $positiona = $request->only('begin_time','over_time','witness_name','witness_position','witness_phone','position');
            $position->update($positiona);
            if($request->info_type == 'ben_position'){
                return $this->lookInformation('ben_position');
            }else{
                return $this->lookInformation('yan_position');
            }
        }elseif ($this->info_type == 'honour'){
            $honour = Honour::find($id);
            $honour->get_time = $request->get_time;
            $honour->honour = $request->honour;
            $honour->update();
            return $this->lookInformation('honour');
        }
    }
    public function delete(Request $request,$type,$id)
    {
        switch ($request->info_type){
            case 'learn':
                $learn = LearnExp::find($id);
                if($learn){
                $is = $learn->delete();
                return $this->ResponDelete(true);                                    
                }else{
                    return $this->ResponDelete(false);
                }
                break;
            case 'work':
                $work = Work::find($id);
                if($work){
                $work->delete();
                return $this->ResponDelete(true);                                    
                }else{
                    return $this->ResponDelete(false);
                }
                break;
            case 'education':
                $edu = Education::find($id);
                if($edu){
                $edu->delete();
                return $this->ResponDelete(true);                                    
                }else{
                    return $this->ResponDelete(false);
                }
                break;
            case 'ben_position':
                $ben = BenPosition::find($id);
                if($ben){
                $ben->delete();
                return $this->ResponDelete(true);                                    
                }else{
                    return $this->ResponDelete(false);
                }
                break;
            case 'yan_position':
                $yan = YanPosition::find($id);
                if($yan){
                $yan->delete();
                return $this->ResponDelete(true);                                    
                }else{
                    return $this->ResponDelete(false);
                }
                
                break;
            case 'honour':
                $honour = Honour::find($id);
                if($honour){
                $honour->delete();
                return $this->ResponDelete(true);                                    
                }else{
                    return $this->ResponDelete(false);
                }
                break;
        }
    }
    public function ResponDelete($data){
        if($data){
            return $this->response->array([
            'statues' => '操作成功'
        ]);
        }else{
            return $this->response->array([
            'statues' => '操作失败，信息或已删除！'
            ]);
        }
    }
    public function lookInformation($info_type)
    {
        $user = \Auth::guard('recruit')->user();
        switch ($info_type){
            case 'information':
                $information['data'] = $user->information;
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

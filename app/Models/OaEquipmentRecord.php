<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaEquipmentRecord extends Model
{
    //
    protected $guarded = ['id'];
    public function device(){
        return $this->belongsTo('App\Models\OaEquipment','device_id','id');
    }

    public function lend_user_name(){
        return $this->belongsTo('App\Models\OaYouthUser','lend_user','sdut_id');
    }
    public function memo_user_name(){
        return $this->belongsTo('App\Models\OaYouthUser','memo_user','sdut_id');
    }
    public function rememo_user_name(){
        return $this->belongsTo('App\Models\OaYouthUser','rememo_user','sdut_id');
    }
}

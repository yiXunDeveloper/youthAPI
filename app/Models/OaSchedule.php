<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaSchedule extends Model
{
    //
    protected $guarded = ['id'];
    public function sponsor_user(){
        return $this->hasOne('App\Models\OaYouthUser','sdut_id','sponsor');
    }

}

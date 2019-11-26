<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaSchedule extends Model
{
    //
    protected $guarded = ['id'];
    public function sponsor_user(){
        return $this->belongsTo('App\Models\OaYouthUser','sponsor','sdut_id');
    }

}

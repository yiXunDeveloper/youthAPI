<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaSigninRecord extends Model
{
    //
    protected $guarded = ['id'];
    public function user(){
        return $this->hasOne('App\Models\OaYouthUser','sdut_id','sdut_id');
    }
}

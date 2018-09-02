<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaYouthUser extends Model
{
    protected $guarded = ['id'];
    //
    public function duty(){
        return $this->hasOne('App\Models\OaSigninDuty','sdut_id','sdut_id');
    }
}

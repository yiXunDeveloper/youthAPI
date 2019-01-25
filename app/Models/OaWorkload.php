<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaWorkload extends Model
{
    //
    protected $guarded = [];
    public function user() {
        return $this->belongsTo('App\Models\OaYouthUser','sdut_id','sdut_id');
    }
}

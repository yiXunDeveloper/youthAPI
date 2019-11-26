<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesCategory extends Model
{
    protected $table = 'ques_categorys';
    //
    protected  $guarded = [];
    public function login_questions()
    {
        return $this->hasMany('App\Models\QuesLoginQuestion','catid','id');
    }
    public function invest_questions()
    {
        return $this->hasMany('App\Models\QuesInvestQuestion','catid','id')->orderBy('input_num','ASC');
    }
    public function user(){
        return $this->belongsTo('App\Models\QuesAdmin','author','id');
    }
    public function answers(){
        return $this->hasMany('App\Models\QuesAnswer','catid','id');
    }
}

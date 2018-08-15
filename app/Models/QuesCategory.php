<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesCategory extends Model
{
    protected $table = 'ques_categorys';
    //
    public $incrementing = false;
    public function fields()
    {
        return $this->hasMany('App\Models\QuesLoginQuestion','catid','id');
    }
    public function questions()
    {
        return $this->hasMany('App\Models\QuesInvestQuestion','catid','id');
    }
}

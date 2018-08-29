<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesInvestQuestion extends Model
{
    //
    protected $guarded = [];

    public function options()
    {
        return $this->hasMany('App\Models\QuesInvestOption','quesid','id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesInvestQuestion extends Model
{
    //
    public function input_options()
    {
        return $this->hasMany('App\Models\QuesInvestOption','quesid','id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QqFans extends Model
{
    protected $table = 'qq_fans';
    
    protected $guarded = ['id'];

    // //关联关注者模型 （一对一）对应一名用户
    // public function QqUserBasic() {
    //     return $this -> hasOne('App\Models\QqUserBasic', 'id', 'user_id');
    // }

}

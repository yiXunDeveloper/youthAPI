<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QqUserInfo extends Model
{
    protected $table = "users_info";
    protected $guarded = ['id', 'qqapp_openid'];

    protected $fillable = [
        'name',
        'school',
        'offical',
        'sex',
        'des',
        'tag',
        'level'
    ];
    // public $timestamps = false;

    //模型的关联操作：关联文章模型 （一对多）
    public function QqArticle()
    {
        //第二参数为关联表字段，第三参数为本表关联字段   等号省略
        return $this->hasMany('App\Models\QqArticle', 'user_id', 'id');
    }
}

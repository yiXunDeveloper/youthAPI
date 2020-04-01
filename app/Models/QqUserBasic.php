<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QqUserBasic extends Model
{
    protected $table = 'qq_users_basic_info';

    protected $guarded = ['id'];

    //模型的关联操作：关联文章模型 （一对多）User -- Article
    public function QqArticle()
    {
        return $this->hasMany('App\Models\QqArticle', 'user_id', 'id');
    }

    //关联评论模型 一个人评论过多篇文章  再根据评论找到对应文章展示出来
    public function QqComment()
    {
        return $this->hasMany('App\Models\QqComment', 'user_id', 'id');
    }

    //关联粉丝   一个用户有多个粉丝
    public function QqFans()
    {
        return $this->hasMany('App\Models\QqFans', 'fans_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QqComment extends Model
{
    protected $table = "qq_comment";

    protected $guarded = ['id'];
    
    // public $timestamps = false;

     //关联文章模型 （一对一）一条评论对应一篇文章
     public function QqArticle() {
        return $this -> hasOne('App\Models\QqArticle', 'id', 'article_id');
    }

     //关联作者模型 （一对一）一条评论对应一位评论者
     public function QqUserBasic() {
        return $this -> hasOne('App\Models\QqUserBasic', 'id', 'user_id');
    }
}

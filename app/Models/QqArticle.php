<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QqArticle extends Model
{
    protected $table = "qq_article";

    protected $guarded = ['id'];

    public $timestamps = true;

    //关联作者模型 （一对一）一篇文章一位作者
    public function QqUserBasic() {
        return $this -> hasOne('App\Models\QqUserBasic', 'id', 'user_id');
    }

    //关联评论模型（一对多）一篇文章多个评论
    public function QqComment() {
        return $this -> hasMany('App\Models\QqComment', 'article_id', 'id');
    }

    //关联评论模型（一对多）一篇文章多个点赞
    public function QqArticleGood() {
        return $this -> hasMany('App\Models\QqArticleGood', 'article_id', 'id');
    }

}

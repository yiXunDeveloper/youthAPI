<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QqCollect extends Model
{
    protected $table = "qq_collection";

    protected $guarded = ['id'];

    public function QqCollect() {
        return $this->hasMany('App\Models\QqArticle', 'id', 'article_id');
    }
}

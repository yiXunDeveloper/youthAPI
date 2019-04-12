<?php

namespace App\Models\Recruit;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'recruit_images';
    protected $fillable = ['type', 'path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

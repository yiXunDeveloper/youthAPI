<?php

namespace App\Models\Recruit;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Auth;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'recruit_users';
    protected $guarded = [];
    protected $guard_name = 'recruit';
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function information()
    {
        return $this->hasOne(Information::class);
    }
    public function learn()
    {
        return $this->hasMany(LearnExp::class);
    }
    public function work(){
        return $this->hasMany(Work::class);
    }
    public function education(){
        return $this->hasMany(Education::class);
    }
    public function ben_position(){
        return $this->hasMany(BenPosition::class);
    }
    public function yan_position(){
        return $this->hasMany(YanPosition::class);
    }
    public function honour(){
        return $this->hasMany(Honour::class,'user_id','id');
    }
    public function avatar($id)
    {
        return Image::find($id);
    }
}

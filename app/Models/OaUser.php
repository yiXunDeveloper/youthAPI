<?php

namespace App\Models;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;
use Spatie\Permission\Traits\HasRoles;

class OaUser extends Authenticatable implements JWTSubject
{
    use HasRoles;
    protected $guard_name = 'oa';
    protected $guarded = ['id'];
    public function userinfo(){
        return $this->hasOne('App\Models\OaYouthUser','sdut_id','sdut_id');
    }
    //
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}

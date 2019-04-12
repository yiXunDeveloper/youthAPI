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
}

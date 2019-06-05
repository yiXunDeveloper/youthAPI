<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;
class ServiceUser  extends Authenticatable implements JWTSubject
{
    //
    protected $guarded = [];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function college() {
        return $this->belongsTo('App\Models\College','college_id','id');
    }
    public function dormitory() {
        return $this->belongsTo('App\Models\Dormitory','dormitory_id','id');
    }
}

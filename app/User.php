<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','mobile','gender','referral_code','date_of_birth'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function findForPassport($identifier) {
        return $this->orWhere('email', $identifier)->orWhere('mobile', $identifier)->first();
    }

    public function referralBy(){
        return $this->hasMany('App\Referral',"referred_by");
    }
    public function referralTo(){
        return $this->hasMany('App\Referral',"referred_to");
    }

    public function rideRequest(){
	return $this->hasMany('App\RideRequest','user_id');
    }
}

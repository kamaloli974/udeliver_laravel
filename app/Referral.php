<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $table="referrals";

    public function refferedToUser(){
        return $this->belongsTo('App\User',"referred_to");
    }

    public function referredByUser(){
        return $this->belongsTo('App\User',"referred_by");
    }
}

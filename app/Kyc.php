<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    protected $fillable = [
        'aadhar_card_front', 'aadhar_card_back', 'driving_licence_front','driving_licence_back','status','user_id'
    ];
   
    protected $table="kyc";
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RideRequest extends Model
{
     protected $fillable = [
        'user_address', 'user_id','latitude', 'longitude','destination_address','destination_latitude','destination_longitude','is_round_trip','status'
    ];

    protected $table="ride_requests";
    public function user(){
        return $this->belongsTo('App\User',"user_id");
    }

}

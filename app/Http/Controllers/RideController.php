<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use App\RideRequest;

class RideController extends Controller
{
    public function requestRide(Request $request){
        $user=Auth::user();
        if ($user){
	 $validator = Validator::make($request->all(), [
                'user_address' => 'required',
                'latitude' => 'required',
                'longitude'=>'required',
                'latitude'=>'required',
                'longitude'=>'required',
                'destination_address'=>'required',
                'destination_latitude'=>'required',
                'destination_longitude'=>'required',
                'is_round_trip'=>'required',
                'status'=>'required']);
		
		$rideRequest=new RideRequest();
		
		$rideRequest->user_address=$request->input('user_address');
		$rideRequest->latitude=$request->input('latitude');
		$rideRequest->longitude=$request->input('longitude');
		$rideRequest->destination_address=$request->input('destination_address');
		$rideRequest->destination_latitude=$request->input('destination_latitude');
		$rideRequest->destination_longitude=$request->input('destination_longitude');
		$rideRequest->is_round_trip=$request->input('is_round_trip');
		$rideRequest->status=$request->input('status');
		$rideRequest->user_id=$user->id;

		$rideRequest->save();
		return response()->json(['message'=>"You are have successfully requested ride. Please wait a while our customer service will cantact you soon. Thank you.","code"=>200]);
	
            if ($validator->fails()){
                return response()->json(['message'=>$validator->errors()->all(),"code"=>423]);
            }
            $rideRequest =RideRequest::create($request->toArray());
        }else{
            return response()->json(['message'=>"You are not authorized user. Please logout and sign in again. Thnak you","code"=>423]);
        }
    }
}

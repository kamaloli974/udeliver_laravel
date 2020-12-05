<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register',['uses'=>'UserController@register']);
Route::post('/login',['uses'=>'UserController@login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/logout', 'UserController@logout')->name('logout');
});

Route::middleware('auth:api')->get('/user',['uses'=>'UserController@getUser']);
Route::get('/hello',function(){
	return "hell";
});

Route::middleware('auth:api')->post('/update_user',["uses"=>"UserController@updateUser"]);
Route::middleware('auth:api')->post('/update_profile_pic',["uses"=>"UserController@updateProfilePic"]);
Route::middleware('auth:api')->post('/referral',["uses"=>"UserController@refferal"]);
Route::middleware('auth:api')->post('/request_ride',["uses"=>"RideController@requestRide"]);
Route::middleware('auth:api')->post('/upload_kyc_document',["uses"=>"UserController@uploadKyc"]);
Route::middleware('auth:api')->get('/get_kyc',["uses"=>"UserController@getKyc"]);
Route::middleware('auth:api')->post('/get_checksum',["uses"=>"PaymentController@getChecksum"]);
Route::middleware('auth:api')->post('/credit_transaction',["uses"=>"PaymentController@creditTransaction"]);
Route::middleware('auth:api')->post('/add_money',["uses"=>"PaymentController@addMoney"]);

Route::get('send_sms',["uses"=>"UserController@sendSms"]);
Route::post('get_event',["uses"=>"RideController@getEvent"]);

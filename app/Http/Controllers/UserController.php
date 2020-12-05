<?php

namespace App\Http\Controllers;

use App\User;
use App\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use App\Kyc;
use GuzzleHttp\Client;
class UserController extends Controller
{
    public function register (Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'mobile'=>'required|string|max:10|unique:users',
	    'referral_code'=>'required|string'
        ]);
	
	$message="Hello ".$request->name."\n Welcome to Yoyp, a hassle free cab booking service. Yoyp Provides a seemless experience in the wold of online cab booking service.";
	$message=urlencode($message);
	$url="http://onextel.in/shn/api/pushsms.php?usr=adwrapp2017&key=010921ro0thdspCNUIZqFcItFFRaH1&sndr=YOYPIN&ph=".$request->mobile."&text=".$message."&rpt=1";


        if ($validator->fails()){
            return response()->json(['errors'=>$validator->errors()->all(),"code"=>423]);
        }

        $request['password']=Hash::make($request['password']);
        $user = User::create($request->toArray());
	
	$guzzleClient=new Client();
	$guzzleClient->request('GET',$url);
	
        $token = $user->createToken('Yoyp Password Grant Client')->accessToken;
        $response = ['token' => $token];

	

        return response()->json(['message'=>'Success','access_token'=>$token,"code"=>200]);
    }

    public function login (Request $request)
    {
        $mobile=$request->input('mobile');
        $otp=$request->input('otp');

        if(isset($mobile)&&isset($otp)){
             $user = User::where('mobile', $request->mobile)->first();
	    if (Hash::check($request->otp, $user->password)) {
                $token = $user->createToken('Roto Password Grant Client')->accessToken;
                return response()->json(['message'=>"Success",'access_token'=>$token,"code"=>200]);
            } else {
                $response = "Incorrect OTP ";
                return response()->json(['message'=>$response,"code"=>422]);
            }
        }else if(isset($mobile)&&!isset($otp)){
            $user = User::where('mobile', $request->mobile)->first();
            if($user){
                $randomValue=(int)(10000*mt_rand()/mt_getrandmax())."";
                $difference=abs(strlen($randomValue)-4);
                if($difference!==0){
                   $randomValue=substr(rand(10000,90000)."",$difference);
                }

                $user->password=Hash::make($randomValue);
                $user->save();
                $message="Please don't share this OTP with anyone. Your OTP is : ".$randomValue;
                $message=urlencode($message);
                $url="http://onextel.in/shn/api/pushsms.php?usr=adwrapp2017&key=010921ro0thdspCNUIZqFcItFFRaH1&sndr=ROTOCB&ph=".$request->mobile."&text=".$message."&rpt=1";
                $guzzleClient=new Client();
	        $guzzleClient->request('GET',$url);
                return response()->json(['message'=>'Success',"code"=>200,"otp"=>$randomValue]);
            }else{
                $newUser=new User();
                $newUser->mobile=$mobile;
                $newUser->is_new_user=1;
                
                $randomValue=(int)(10000*mt_rand()/mt_getrandmax())."";
                $difference=abs(strlen($randomValue)-4);
                if($difference!==0){
                   $randomValue=substr(rand(10000,90000)."",$difference);
                }
                
                $newUser->password=Hash::make($randomValue);
                $newUser->save();
               
                $message="Please don't share this OTP with anyone. Your OTP is : ".$randomValue;
                $message=urlencode($message);
                $url="http://onextel.in/shn/api/pushsms.php?usr=adwrapp2017&key=010921ro0thdspCNUIZqFcItFFRaH1&sndr=ROTOCB&ph=".$request->mobile."&text=".$message."&rpt=1";
                $guzzleClient=new Client();
                $guzzleClient->request('GET',$url);
                return response()->json(['message'=>'Success',"code"=>200,"otp"=>$randomValue]);
            }
        }
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = 'You have been successfully logged out!';
        return response()->json(['message'=>$response,"code"=>200]);

    }

    public function getUser(){
        $user=Auth::user();
        return response()->json(['user'=>$user,"code"=>200]);
    }

    public function updateUser(Request $request){
        $user=Auth::user();
        $token = $request->user()->token();
        if ($user) {
	    $fullName=$request->input('name');
            $email=$request->input('email');
            $gender=$request->input('gender');
            $dateOfBirth=$request->input('date_of_birth');
            $user->email=$email;
            $user->gender=$gender;
            $user->date_of_birth=$dateOfBirth;
	    $user->name=$fullName;
	    $user->is_new_user=$request->input("is_new_user");		
	    $user->referral_code=$request->input("referral_code");
            $user->save();
            return response()->json(['message'=>"Your request is completed successfully","code"=>200,"result"=>$user]);
        }else {
            return response()->json(['message'=>"User does't exist","code"=>422]);
        }
        
    }	

    public function updateProfilePic(Request $request){
        $user=Auth::user();

        if ($user) {
            $user->image=$request->input('image_url');
            $user->save();
            return response()->json(['message'=>"You have successfully updated your profile pic.","code"=>200]);
        }
        else {
            return response()->json(['message'=>"User does't exist","code"=>422]);
        }
    }

    public function refferal(Request $request){
        $user=Auth::user();
        if ($user) {
             $referralCode=$request->input('referral_code');
             if ($user->is_referral_code_entered==0) {
                 $referralUser=User::where("referral_code",$referralCode)->firstOrFail();
                 if ($referralUser) {
                     $referralUser->wallet=($referralUser->wallet)+50;
                     $user->wallet=($user->wallet)+50;
                     $referral=new Referral();
                     $referral->amount=50;
                     $referral->referred_by=$referralUser->id;
                     $referral->referred_to=$user->id;
                     $referral->save();
                     $user->is_referral_code_entered=1;
                     $referralUser->is_referral_code_entered=1;
                     $referralUser->save();
                     $user->save();
                     return response()->json(['message'=>"COngratulations!! you have benifitted Rs. 50. Please keep using app and refer your friend to earn more.","code"=>200,"wallet"=>$user->wallet]);
                 }else {
                    return response()->json(['message'=>"Invalid referral code. Please enter valid referral code and try again. Thank you.","code"=>424]);
                 }
             }else{
                return response()->json(['message'=>"You have already used one referral code. To earn more please refer your friend and have them enter your referal code and then you will also be benifited.","code"=>422]);
             }
        }else{
            return response()->json(['message'=>"You are not authorized user. Please logout and sign in again. Thnak you","code"=>423]);
        }  
    }    
    
    public function uploadKyc(Request $request){
        $user=Auth::user();
        $selection=$request->input('selection');

        $kyc=Kyc::where("user_id",$user->id)->first();

        if (!$kyc) {
            $kyc=new Kyc();
	    $user->kyc_status=1;
	    $user->save();
        }

        if ($selection==1) {
            $kyc->aadhar_card_front=$request->input('value');
	    $kyc->aadhar_card_front_status=1;

        }else if ($selection==2) {
            $kyc->aadhar_card_back=$request->input('value');
	    $kyc->aadhar_card_back_status=1;
        }else if ($selection==3) {
            $kyc->driving_licence_front=$request->input('value');
	    $kyc->driving_licence_front_status=1;
        }else if($selection==4){
            $kyc->driving_licence_back=$request->input('value');
	    $kyc->driving_licence_back_status=1;
        }
	else if($selection==5){
            $kyc->selfi=$request->input('value');
	    $kyc->selfi_status=1;
        }
	

        $kyc->user_id=$user->id;

        $kyc->save();

        $kycValue=Kyc::where('user_id',$user->id)->first();
        
        return response()->json(['message'=>'You have successfully uploaded your kyc document.','code'=>200,'kyc'=>$kycValue]);
    }


    public function getKyc(){
	$user=Auth::user();
	$kyc=Kyc::where('user_id',$user->id)->first();
	return response()->json(['message'=>'Your request has been processed successfully','code'=>200,'kyc'=>$kyc]);	
    }

    
    public function sendSms(){
	$url="http://onextel.in/shn/api/pushsms.php?usr=adwrapp2017&key=010921ro0thdspCNUIZqFcItFFRaH1&sndr=YOYPIN&ph=9872546185&text=Hello Deep Ji Ye Test Message He&rpt=1";
	$guzzleClient=new Client();
	$response=$guzzleClient->request('GET',$url);
	return response()->json($response);

    }

}

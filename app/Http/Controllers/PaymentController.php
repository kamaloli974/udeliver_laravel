<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
class PaymentController extends Controller
{
    public function getChecksum(Request $request){
	$merchantId=$request->input('merchant_id');
	include(app_path() . '/lib/config_paytm.php');
	include(app_path() . '/lib/encdec_paytm.php');
	$paramList = array();

	$paramList["MID"] = $request->input("merchant_id");
	$paramList["ORDER_ID"] = $request->input("order_id");
	$paramList["CUST_ID"] = $request->input("customer_id");
	$paramList["INDUSTRY_TYPE_ID"] = $request->input("industry_type_id");
	$paramList["CHANNEL_ID"] = $request->input("channel_id");
	$paramList["TXN_AMOUNT"] = $request->input("txt_amount");
	$paramList["WEBSITE"] = $request->input("website");
	$paramList["MOBILE_NO"]=$request->input("mobile");
	$paramList["EMAIL"]=$request->input("email");
	$paramList["CALLBACK_URL"]=$request->input("callback_url");
	$checkSum = getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);
	return response()->json(["code"=>200,"message"=>"Success ","checksum"=>$checkSum,"data"=>$paramList,"merchant_key"=>PAYTM_MERCHANT_KEY,"request"=>$request->toArray()]);
    }

    public function creditTransaction(Request $request){
        $validator = Validator::make($request->all(), [
            'credit' => 'required',
            'mode_of_payment' => 'required',
            'merchant' => 'required',
            'merchant_transaction_id'=>'required',
            'status'=>'required'
        ]);

        if ($validator->fails()){
            return response()->json(['message'=>$validator->errors()->all(),"code"=>423]);
        }

        $user=Auth::user();

        $transaction=new Transaction();

        $transaction->credit=$request->input('credit');
        $transaction->mode_of_payment=$request->input('mode_of_payment');
        $transaction->merchant=$request->input('merchant');
        $transaction->merchant_transaction_id=$request->input('merchant_transaction_id');
        $transaction->status=$request->input('status');

        $transaction->save();

        $user->wallet=($user->wallet)+($request->input('credit'));
	return response()->json(["message"=>"Your transaction is successfully completed. Thank you for user our service.","code"=>200,"wallet"=>$user->wallet]);
    }
   
    public function addMoney(Request $request){
	$user=Auth::user();
	$user->wallet=($user->wallet)+($request->input('wallet'));
	$user->save();
	return response()->json(["message"=>"Your transaction is successfully completed. Thank you for user our service.","code"=>200,"wallet"=>$user->wallet]);
    }

}

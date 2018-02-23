<?php

namespace App\Http\Controllers\Api;

use App\Dibi;
use App\Pledge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    //

    public function login(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->getMessageBag(),400);
        }

        $user = Dibi::where('username', '=', $request['username']);


        if ($user->count()) {

            if (Hash::check($request['password'], $user->first()->password)) {
                return response()->json($user->first(), 200);
            } else {
                return response()->json(['Incorrect Password'], 201);
            }

        }
        return response()->json(['User does not exist'],404);


    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
           'name'=>'required',
            'username'=>'required|unique:dibis',
            'phone'=>'required|unique:dibis',
            'password'=>'required|min:6',
            'gender'=>'required'

        ]);

        if($validator->fails()){
            return response()->json($validator->getMessageBag(),400);
        }

        $image = 'male_avatar.png';

        if(strtolower($request['gender']) == 'female'){
            $image = 'female_avatar.png';
        }

        $code = substr(uniqid(),0,6);

        $dibi = Dibi::create([
            'name'=>$request['name'],
            'username'=>$request['username'],
            'phone'=>$request['phone'],
            'password'=>bcrypt($request['password']),
            'image'=>$image,
            'gender'=>$request['gender'],
            'tokens'=>20,
            'verification_code'=>$code
        ]);

        if($dibi){

            $title = 'Mr. ';
            if(strtolower($request['gender']) == 'female'){
                $title = 'Miss. ';
            }

            $msg = 'Welcome ' . $title . $request['name'] . ' to Di-Bi, this is your account verification code '.$code;

            $this->sendSMS($request['phone'],$msg);

            return response()->json($dibi, 200);

        }
        return response()->json(['could not register new user'], 201);

    }

    public function verify(Request $request){
        $id = $request['id'];
        $token = $request['code'];

        $user = Dibi::find($id);

        if($user){
            if($token == $user->verification_code){
                $user->update(['status'=>1]);

                return response()->json($user,200);
            }
            return response()->json(['Incorrect token'], 201);

        }

        return response()->json(['User not found'], 404);

    }

    public function buyTokens(Request $request){
        /*$user = Dibi::find($request['id']);

        if(!$user){
            return response()->json(['User not found'], 404);
        }*/

        $amount = $request['amount'];
        $tokens = $request['tokens'];
        $phone = $request['phone'];
        $provider = $request['provider'];


        $receive_momo_request = array(
            'CustomerName' => 'Sanson',
            'CustomerMsisdn'=> $phone,
            'CustomerEmail'=> 'sanson@gm.com',
            'Channel'=> $provider,
            'Amount'=> $amount,
            'PrimaryCallbackUrl'=> 'http://5d56a10b.ngrok.io/api/momo/callback',
            'Description'=> 'Buying Tokens',
            'ClientReference'=> 'dibi'
        );

        $clientId = 'rpqaxumo';
        $clientSecret = 'yipdqwso';
        $basic_auth_key =  'Basic ' . base64_encode($clientId . ':' . $clientSecret);
        $request_url = 'https://api.hubtel.com/v1/merchantaccount/merchants/HM1808170038/receive/mobilemoney';
        $receive_momo_request = json_encode($receive_momo_request);

        $ch =  curl_init($request_url);
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $receive_momo_request);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: '.$basic_auth_key,
            'Cache-Control: no-cache',
            'Content-Type: application/json',
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if($err){
            echo $err;
        }else{
            echo $result;
        }

    }

    private function sendSMS($phone, $message)
    {
        $key = 'e845c4d9cf21d107b5b6';
        $msg = urlencode($message);

        $url = "https://apps.mnotify.net/smsapi?key=$key&to=$phone&msg=$msg&sender_id=Di-Bi";

        $result = file_get_contents($url);

        switch ($result) {
            case "1000":
                echo 'success';
                break;

        }
    }

    public function updateImage(Request $request)
    {
        $dibier = Dibi::find($request['id']);

        if ($dibier) {
            $destinationPath = 'img/uploads/';

            if ($request->hasFile('image')) {

                $path = uniqid() . '.' . $request->file('image')->extension();
                //$file->move('img/uploads',$path);
                $request->file('image')->move($destinationPath, $path);
                if ($dibier->update(['image' => $path])) {
                    return response()->json($dibier, 200);
                }
                return response()->json(['Error Changing Profile Image'], 201);

            }
        }

        return response()->json(['User not found'], 404);

    }

    public function pledge($id){
        $user = Dibi::find($id);

        if($user->tokens < 20){
            return response()->json(['Not enough tokens to pledge'],201);
        }

        if($user->active == 0){
            return response()->json(['Sorry, this accounts is deactivated'],202);
        }

        $pledge = Pledge::create([
            'pledger_id'=>$id,
            'amount'=>100.0,
            'plegde_status'=>1
        ]);

        if($pledge){
            return response()->json($pledge,200);
        }

        return response()->json(['Could not pledge, server error'],500);


    }

    public function confirmPledge($pledger_id,$id){

    }

    public function rematchPledge($id){

    }

    public function momoCallback(Request $request){

    }

    public function pendingTransactions($type,$id){

        if($type == 'pending'){
            $transactions = Pledge::where('pledger_id',$id)->where('plegde_status',1)->get();

            return response()->json($transactions,200);

        }

        if($type == 'incoming'){
            $transactions = Pledge::where('receiver_id',$id)->where('receive_status',1)->get();

            return response()->json($transactions,200);
        }

        if($type == 'complete'){
            $transactions = Pledge::where('receiver_id',$id)->where('receive_status',1)
                ->orWhere('pledger_id',$id)->where('pledge_status',1)->get();

            return response()->json($transactions,200);
        }


    }

    public function updateWallet($id,Request $request){
        $user = Dibi::find($id);

        if($user){
            $provider = strtolower($request['provider']).'-gh';

            $wallet = Wallet::create([
                'dibi_id'=>$id,
                'name'=>$request['name'],
                'phone'=>$request['phone'],
                'provider'=>$provider
            ]);
            $user->update([
                'wallet'=>$wallet->id
            ]);

            return response()->json(Dibi::find($id),200);
        }
        return response()->json(['User not found'],404);
    }

}

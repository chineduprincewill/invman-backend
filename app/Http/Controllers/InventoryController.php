<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Inventory;
use App\User;

class InventoryController extends Controller
{
    private $SMS_SENDER = "INVENTORY";
    private $RESPONSE_TYPE = 'json';
    private $SMS_USERNAME = 'info@hezekiah.edu.ng';
    private $SMS_PASSWORD = 'nazzyhez4real';

    //
    public function index()
    {
        return Inventory::orderBy('created_at', 'desc')->get();
    }


    public function store(Request $request)
    {

        $inventory = Inventory::create([
            'item' => $request->get('item'),
            'description' => $request->get('descripiton'),
            'quantity' => $request->get('quantity'),
            'date_in' => $request->get('date_in'),
            'purchased_by' => $request->get('purchased_by'),
            'last_disbursed' => "",
            'quantity_disbursed' => 0,
            'date_disbursed' =>"",
            'disbursed_to' => "",
            'purpose' =>"",
            'current_quantity' =>  $request->get('quantity'),
            'created_by' => $request->get('created_by'),
        ]);

       // $token =  JWTAuth::fromUser($user);

        $email = $request->get('created_by');
        //return response()->json(compact('user', 'token'), 201);
        $get_user_mobile = User::where("email", $email)->first();
        $phone_number = $get_user_mobile->mobile;

        if($inventory){

            // SEND EMAIL

            // SEND SMS
            $message = $request->get('quantity')." ".$request->get('item')."(s) was added to Inventory today being ".date("Y-m-d");
            $this->send_sms($phone_number, $message);

            return response()->json([
                'success'=> true,
                'message' => 'Inventory succesfully added!'
            ], 201);
            
            //$url = 'https://payhub-client.netlify.app';

            //$this->UserCreatedEmailAlert($user->email, $url, $request->get('username'), $request->get('password'));

        }
        else{
            return response()->json([
                'success'=> false,
                'message' => 'Sorry! Inventory could not be added'
            ], 500);
        }
    }

    public function show($id)
    {
        return Inventory::find($id);
    }


    public function update(Request $request, $id)
    {

        
        $inventory = Inventory::find($id);

        if($inventory)
        {
            
            if($inventory->fill($request->all())->save()){

                return response()->json([
                    'success' => true,
                    'message' => 'Inventory successfully updated!'
                ], 201);
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory could not be updated!'
                ], 401);
            }
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Inventory information could not be updated!'
            ], 404);
        }

    }


    public function initiateSmsGuzzle($phone_number, $message)
    {
        $client = new Client();

        $response = $client->post('http://api.smartsmssolutions.com/smsapi.php', [
            'verify'    =>  false,
            'form_params' => [
                'username' => $this->SMS_USERNAME,
                'password' => $this->SMS_PASSWORD,
                'message' => $message,
                'sender' => $this->SMS_SENDER,
                'mobiles' => $phone_number,
            ],
        ]);


        $response = json_decode($response->getBody(), true);
    }


    public function send_sms($phone_number, $message)
    {

        //$message = 'Test message';
        $senderid = 'INVENTORY';
        $to = $phone_number;
        $token = 'wVQ5Ya8rJruftPIfcAMg1yZClFIYiKm3kLDDq7pOCgH4CFKx088d4O4UJvq04E3masoZBFlsphRtsHXoMjZ3kEikHbpOL7I2MIMc';
        $baseurl = 'https://smartsmssolutions.com/api/json.php?';

        $sms_array = array 
        (
        'sender' => $senderid,
        'to' => $to,
        'message' => $message,
        'type' => '0',
        'routing' => 3,
        'token' => $token
        );

        $params = http_build_query($sms_array);
        $ch = curl_init(); 

        curl_setopt($ch, CURLOPT_URL,$baseurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $response = curl_exec($ch);

        curl_close($ch);

        echo $response; // response code

    }
}

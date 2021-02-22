<?php

namespace App\Http\Controllers;

use App\User;

use App\Mail\CustomerCreated;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class UserController extends Controller
{
    
    /**
     *  Fetch all users
     */
    public function index($role)
    {
        
        if($role == 'admin'){

            return User::orderBy('created_at', 'desc')->get();
        }
        else if($role == 'teller'){

            return User::where('role', 'customer')->orderBy('created_at', 'desc')->get();
        }
        
    }


    public function getReps($role)
    {

        if($role != 'admin' && $role != 'teller'){

            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }

        $reps = User::where('role', 'marketer')->orderBy('created_at', 'desc')->get();

        return response()->json($reps);

    }



    public function customers()
    {    

        $customers = User::where('role', 'customer')->orderBy('created_at', 'desc')->get();

        return response()->json($customers);

    }


    public function customersByMarketer($marketer)
    {
        $customers = User::where('rep', $marketer)->orderBy('created_at', 'desc')->get();

        return response()->json($customers);
    }


    public function customersByZerobalance()
    {   
        $balance = 0.00;
        $customers = User::where('accountbalance', $balance)->where('role', 'customer')->orderBy('created_at', 'desc')->get();

        return response()->json($customers);
    }


    public function customersByDate($date)
    {   
        
        $customers = User::where('created_at', 'like', $date.'%' )->where('role', 'customer')->get();

        return response()->json($customers);
    }


    public function tellers($role)
    {
        $access = $this->checkAccessibility($role);
        
        if(!$access){
            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }

        $tellers = User::where('role', 'teller')->orderBy('created_at', 'desc')->limit(30)->get();

        return response()->json($tellers);
    }


    /**
     *  Login User
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try{
            if(! $token =  JWTAuth::attempt($credentials)){
                return response()->json(['error' => 'invalid credentials'], 400);
            }  
            if(User::where('email', $request->email)
                    ->where('status', 1)->first()){
                        return response()->json(['error' => 'Unauthorized access'], 400);
                    }
        } catch(JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json(compact('token', 'user'));
    }

    /**
     *  Register User
     */
    public function register(Request $request, $role)
    {

        if($role != 'admin' && $role != 'teller'){

            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }

        if($role == 'teller' && $request->get('role') != 'customer'){

            return response()->json([
                'message' => 'Unauthorized access! You can only register a customer'
            ], 401);
        }


        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        if($role == 'admin'){
            $status = 1;
        }
        else{
            $status = 0;
        }

        $user = User::create([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'username' => $request->get('username'),
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'othernames' => $request->get('othernames'),
            'mobile' => $request->get('mobile'),
            'gender' => $request->get('gender'),
            'station' => $request->get('station'),
            'role' => $request->get('role'),
            'status' => $status,
            'last_login' => time(),
            'created_by' => $request->get('created_by'),
            'login_status' => 0,
        ]);

       // $token =  JWTAuth::fromUser($user);

        //return response()->json(compact('user', 'token'), 201);

        if($user){

            if($user->role == 'customer'){
                
                return $this->generateAccountNumber($user->id);
            }
            else{
                return response()->json([
                    'message' => 'User succesfully created!'
                ], 201);
            }
            
            //$user_id = $user->id;
            if($user->email != '' && $user->email != NULL){

                $url = 'https://payhub-client.netlify.app';

                $this->UserCreatedEmailAlert($user->email, $url, $request->get('username'), $request->get('password'));
            }

        }
        else{
            return response()->json([
                'message' => 'Sorry! User could not be created'
            ], 500);
        }
    }


    public function UserCreatedEmailAlert($receiver, $url, $username, $password)
    {

        Mail::to($receiver)->send(new CustomerCreated($url, $username, $password));
    }


    public function resetPassword(Request $request)
    {
        $user = User::find($request->id);

        //$current = Hash::make($request->current_pwd);

        //if($current != $user->password){
        //    return response()->json('Incorrect current password!');
        //}

        //$user->password = Hash::make($request->new_pwd);
        //$user->save();

        //return response()->json('Password successfully updated!');

        if (!(Hash::check($request->get('current_pwd'), $user->password))) {
            // The passwords matches
            return response()->json('Incorrect current password!');
        }

        if(strcmp($request->get('current_pwd'), $request->get('new_pwd')) == 0){
            //Current password and new password are same
            return response()->json('New Password cannot be same as your current password. Please choose a different password.');
        }

        //Change Password
        $user->password = bcrypt($request->get('new_pwd'));
        $user->save();
        
        return response()->json('Password successfully updated!');
    }  


    public function retrievePassword( $id, $role)
    {
        $access = $this->checkAccessibility($role);
        
        if(!$access){
            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }

        $user = User::find($id);

        if($user)
        {
            $user->password = Hash::make('1234567');
            $user->save();

            return $this->index($role);
            //return response()->json(User::all());
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'User password could not be reset!'
            ], 401);
        }
        


    }


    /**
     *  GET LOGGED IN USER PROFILE
     */
    public function getAuthenticatedUser()
    {
        try{
            if(! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch(TokenExpiredException $e){
            return response()->json(['token_expired'], 419);
        } catch(TokenInvalidException $e){
            return response()->json(['token_invalid'], 498);
        } catch(JWTException $e){
            return response()->json(['token_absent'], 419);
        }

        return response()->json(compact('user'));
    }


    public function show($id)
    {
        /**
        * check if user has privilege
        */

        return User::find($id);

    }



    public function update(Request $request, $id, $role)
    {

        $access = $this->checkAccessibility($role);
        
        if(!$access){
            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = User::find($id);

        if($user)
        {
            
            $user->fill($request->all())->save();

            return $this->index($role);
            //return response()->json(User::all());
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'User could not be updated!'
            ], 401);
        }

    }


    public function updateProfile(Request $request, $id)
    {
        
        $user = User::find($id);

        if($user)
        {
            
            $user->fill($request->all())->save();

            return response()->json([
                'success' => true,
                'message' => 'User successfully updated!'
            ], 201);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'User could not be updated!'
            ], 401);
        }

    }


    public function deleteAccount($id, $role)
    {

        $access = $this->checkAccessibility($role);
        
        if(!$access){
            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = User::find($id)->delete();

        if($user)
        {
            

            return $this->index($role);
            //return response()->json(User::all());
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'User could not be deleted!'
            ], 401);
        }

    }


    public function activation(Request $request, $id, $role)
    {
        $access = $this->checkAccessibility($role);
        
        if(!$access){
            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = User::find($id);

        if($user)
        {
            $user->status = $request->status;
            $user->save();

            return $this->index($role);
            //return response()->json(User::all());
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'User could not be updated!'
            ], 401);
        }
    }


    
    public function advancedSearch($input, $role)
    {
        $query = User::query();

        
        
        $columns = Schema::getColumnListing('users');
        //$columns = ['firstname', 'lastname', 'othernames', 'email', 'role', 'mobile', 'accountbalance', 'created_at'];

        foreach($columns as $column){

            if($role == 'teller'){
                $query->orWhere($column, 'LIKE', '%' . $input . '%')->where('role', 'customer');
            }
            else if($role == 'admin'){

                $query->orWhere($column, 'LIKE', '%' . $input . '%');
            }

        }

        $users = $query->get();

        return response()->json($users);
    }



    public function tellerSearch($input, $role)
    {
        
        $access = $this->checkAccessibility($role);
        
        if(!$access){
            return response()->json([
                'message' => 'Unauthorized access'
            ], 401);
        }

        $query = User::query();
        
        $columns = Schema::getColumnListing('users');
        //$columns = ['firstname', 'lastname', 'othernames', 'email', 'role', 'mobile', 'accountbalance', 'created_at'];

        foreach($columns as $column){

            $query->orWhere($column, 'LIKE', '%' . $input . '%')->where('role', 'teller');

        }

        $tellers = $query->get();

        return response()->json($tellers);
    }



    public function checkAccessibility($role)
    {
        if($role != 'admin')
        {
            return false;
        }
        else{
            return true;
        }
    }

}

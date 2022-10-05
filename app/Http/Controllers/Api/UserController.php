<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;
 /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(Request $request){ 
        //print_r(url('/'));die();
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email',
            'password' => 'required',
            'usertype' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) { 
             return response()->json(['error'=>$validator->errors()], 401);  
        }
        
        if(Auth::attempt(['email' => request('email'), 'password' => request('password'), 'usertype' => request('usertype')])){ 
            $user = Auth::user(); 
            //if($user->is_active){
            
            //Update the user lat and long on login
                $user_data = User::findOrFail($user->id);
                $user_data->latitude = request()->input('latitude');
                $user_data->longitude = request()->input('longitude');
                $user_data->address = request()->input('address');
                $user_data->save();
            
                $userInfo['name'] =  $user->name;
                $userInfo['email'] =  $user->email;
                $userInfo['user_id'] =  $user->id;
                $userInfo['profile_picture'] =  url('/') . '/uploads/' .$user->profile_picture;
                $userInfo['phone'] =  $user->phone;
                $userInfo['is_updated'] =  $user->is_updated;
                $userInfo['address'] =  $user->address;
                $response_data['userInfo'] =  $userInfo;
                $response_data['token'] =  $user->createToken('EatCentralLaravelAppByElabdTech')-> accessToken; 
                return response()->json(['data' => $response_data], $this-> successStatus); 
                
//            }
//            else{
//                return response()->json(['error'=>'Inactive', 'message' => 'Your account is not active yet.'], 401); 
//            }
            
        } 
        else{ 
            return response()->json(['error'=>'Unauthorised', 'message' => 'Incorrect email or password'], 401); 
        } 
    }
 
    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'usertype' => 'required',
            'phone' => 'required|unique:users',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) { 
             return response()->json(['error'=>$validator->errors()], 401);            
        }
        
        $input = $request->all(); 
        $input['profile_picture'] = 'default.png'; 
        $input['password'] = bcrypt($input['password']); 
        $input['username'] = strstr($input['email'], '@', true) . rand(100,999); 
        //unset($input['confirm_password']);      //remove confirm_password var from list to avoid conflict in database
        //print_r($input);die();
        $user = User::create($input); 
        
    //Update data
        
        $user_data = User::findOrFail($user->id);
        $user_data->latitude = request()->input('latitude');
        $user_data->longitude = request()->input('longitude');
        $user_data->address = request()->input('address');
        $user_data->usertype = request()->input('usertype');
        $user_data->save();
        
        
        $response_data['user_id'] =  $user->id;
        $response_data['token'] =  $user->createToken('EatCentralLaravelAppByElabdTech')-> accessToken; 
        return response()->json(['data'=>$response_data], $this-> successStatus); 
    }
 
}

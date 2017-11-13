<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Login function
     *
     * When user login successfully, it will retrieve a callback as api_token
     */
    public function login(Request $request){
    	$theHash = app()->make('hash');

    	$email = $request->input('email');
    	$password = $request->input('password');

    	$dataUser = User::where('email', $email)->first();
    	if($dataUser){
    		if ($theHash->check($password, $dataUser->password)) {
    			$apiToken = md5(time());
    			$updateTokenDb = User::find($dataUser->id)->update(['api_token' => $apiToken]);
    			if ($updateTokenDb) {
                    $response['success'] = true;
                    $response['api_token'] = $apiToken;
                    $response['message'] = $dataUser;

                    return response($response);
    			}else{
    				$response['success'] = false;
	                $response['message'] = 'Unknown Error!';

	                return response($response);
    			}
    		}else{
                $response['success'] = false;
                $response['message'] = 'Incorrect Email or Password!';

                return response($response);
            }
    	}else{
			$response['success'] = false;
	        $response['message'] = 'We did not recognize your Email or Password!';

	        return response($response);
    	}
    }
}
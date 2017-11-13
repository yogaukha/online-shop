<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
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
     * Register new User
     *
     * @param $request Request
     */
    public function reg(Request $request){
        $theHash = app()->make('hash');

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $theHash->make($request->input('password'));
        $role = $request->input('role');

        $reg = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);

        if ($reg) {
            $response['success'] = true;
            $response['message'] = 'Registration Succeed!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Registration Failed!';

            return response($response);

        }
    }

    /**
     * Get the user data by ID
     *
     * @param $request Request
     * @url /user/{id} 
     */
    public function getUser(Request $request, $id){
        $user = User::findOrFail($id);
        if ($user) {
            $response['success'] = true;
            $response['message'] = $user;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'The user does not exist!';

            return response($response);

        }
    }

    /**
     * Validate User using their api_token
     *
     * @param $request Request
     * @url /user/api/{api_token}
     */
    public function getUserApi(Request $request, $api_token){
        $user = User::where('api_token', $api_token)->first();
        if ($user) {
            $response['success'] = true;
            $response['message'] = $user;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'API KEY Invalid!';

            return response($response);

        }
    }
}

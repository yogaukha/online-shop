<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            if($request->has('api_token')){
                $tokenForm = $request->input('api_token');
                $tokenDb = User::where('api_token', $tokenForm)->first();
                if(empty($tokenDb)){
                    $response['success'] = false;
                    $response['message'] = 'Mismatch Token!';

                    return response($response);
                }
            }else{
                $response['success'] = false;
                $response['message'] = 'Please login first!';

                return response($response);
            }
        }

        return $next($request);
    }
}

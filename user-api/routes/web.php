<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
// $router->get('/', function () use ($router) {
//   $response['success'] = true;
//   $response['result'] = "Howdy, This is Testing of The Online Shop API";
//   return response($response);
// });
// $router->post('/login', 'LoginController@login');
// $router->post('/register', 'UserController@reg');
// $router->get('/user/{id}', ['middleware' => 'auth', 'uses' =>  'UserController@getUser']);
$router->group(['prefix' => 'api/v1'], function () use ($router) {
		$router->get('/', function () {
			$response['success'] = true;
			$response['result'] = "Howdy, This is Testing of The Online Shop API";
			return response($response);
		});
		$router->post('login', 'LoginController@login');
		$router->post('register', 'UserController@reg');
		$router->get('user/{id}', ['middleware' => 'auth', 'uses' =>  'UserController@getUser']);
		$router->get('user/api/{api_token}', 'UserController@getUserApi');
	}
);
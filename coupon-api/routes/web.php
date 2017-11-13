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

/* Route coupon */
$router->group(['prefix' => 'api/v1'], function () use ($router) {
	$router->get('/', function () {
		$response['success'] = true;
		$response['result'] = "Howdy, This is Testing of The Online Shop API";
		return response($response);
	});
	$router->get('/coupon', 'CouponController@index');
	// $router->get('/coupon/{id}', 'CouponController@getCoupon');
	$router->get('/coupon/{code_name}', 'CouponController@getCouponCodeName');
	$router->get('/coupon/delete/{id}', 'CouponController@delete');
	$router->get('/coupon/update-qty/{coupon_id}/{new_qty}', 'CouponController@updateQty');
	$router->post('/coupon/create', 'CouponController@create');
	$router->post('/coupon/update/{id}', 'CouponController@update');
});
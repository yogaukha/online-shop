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
		$router->get('cart/get-cart', 'CartController@getCart');
		$router->get('cart/get-cart-json', 'CartController@getCartJson');
		$router->get('cart/add-to-cart/{product_id}/{qty}', 'CartController@addToCart');
		$router->get('cart/remove-product/{product_id}', 'CartController@removeProductFromCart');
		$router->get('cart/delete-cart', 'CartController@delete');
		$router->get('cart/remove-coupon', 'CartController@removeCoupon');
		$router->get('cart/truncate', 'CartController@truncate');
		$router->post('cart/submit-coupon', 'CartController@submitCoupon');
	}
);
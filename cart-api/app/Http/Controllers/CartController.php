<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cart;
use App\CartDetail;
use GuzzleHttp\Client;
use DateTime;

class CartController extends Controller
{
    const PRODUCT_ENDPOINT = 'http://localhost:3131/api/v1/product/';
    const USER_ENDPOINT = 'http://localhost:3132/api/v1/user/';
    const COUPON_ENDPOINT = 'http://localhost:3134/api/v1/coupon/';
    protected $api_token;
    protected $client;
    protected $loggedUser;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        // $this->middleware('auth');
        //get token
        $this->api_token = $request->header('api_token');

        //add api_token to the headers.
        $this->client = new Client([
            'verify' => false, 
            'headers' => [
                'Accept' => 'application/json',
                'api_token' => $this->api_token
            ]
        ]);

        //kick out the illegal user
        $theUser = $this->client->get(self::USER_ENDPOINT . 'api/' . $this->api_token);
        $this->loggedUser = json_decode($theUser->getBody());
        if($this->loggedUser->message == 'API KEY Invalid!' || $this->loggedUser->message == 'Please login first!'){
            die($this->loggedUser->message);
        }

    }

    /**
     * get Cart
     *
     * @param $request Request, $id
     * url: /cart/get-cart
     */
    public function getCart(){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();

        if ($cart) {
            $total_amount = 0;
            $weight = 0;
            $cartDetail = CartDetail::where('cart_id', $cart->id)->get();

            foreach ($cartDetail as $key => $value) {
                // @TODO this should have to implements Batch, right now it's use one by one call Product Microservice to ease my work 
                $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $value->product_id);
                $product = json_decode($theProduct->getBody());
                $value->setAttribute('product_name', $product->name);
                $value->setAttribute('product_weight', $product->weight);
                $total_amount += $product->price * $value->qty;
                $weight += $product->weight * $value->qty;
            }
            //using gram, so it must divides by 1000, and 14000 is the average shipment fee from Jogja to Jakarta per kg
            $cart->shipment_fee = $weight * 14000 / 1000;
            $cart->total_amount = $total_amount;
            $cart->grand_total = $cart->shipment_fee + $cart->total_amount;
            if ($cart->coupon_id) {
                // include coupon discount
                $cart->grand_total = $cart->shipment_fee + $cart->total_amount - $cart->coupon_discount;
                //save the coupon detail in cart
                $cart->coupon_id = $cart->coupon_id;
                $cart->coupon_code_name = $cart->coupon_code_name;
                $cart->coupon_discount = $cart->coupon_discount;
            }

            if ($cart->save()) {
                $cart->items = !$cartDetail->isEmpty() ? $cartDetail : array('Let`s Start Shopping');
                $response['success'] = true;
                $response['cart'] = $cart;

                return response($response);
            }else{
                $response['success'] = false;
                $response['message'] = 'Unknown Error!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Your cart is empty! Check our products and happy shopping :)';

            return response($response);
        }
    }

    /**
     * Add to cart
     *
     * @param $request Request, $customer_id, $product_id, 
     * @url /cart/add-to-cart/{product_id}/{qty}
     */
    public function addToCart(Request $request, $product_id, $qty){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();
        $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $product_id);
        $product = json_decode($theProduct->getBody());
        // check availability of the product
        if ($product->sold == 1 || $product->stock < 1) {
            $response['success'] = false;
            $response['message'] = 'Product have been sold!';

            return response($response);
        }
        if($qty > $product->stock){
            $response['success'] = false;
            $response['message'] = 'The stock of this product is ' . $product->stock . ' left!';

            return response($response);
        }
        if (!$cart) {
            $cart = new Cart;
        }
        $cart->fill([
            'coupon_id' => $request->input('coupon_id'),
            'coupon_code_name' => $request->input('coupon_code_name'),
            'coupon_discount' => $request->input('coupon_discount'),
            'customer_id' => $this->loggedUser->message->id,
            'customer_name' => $this->loggedUser->message->name,
            'customer_email' => $this->loggedUser->message->email,
            'total_amount' => $qty * $product->price,
            'shipment_fee' => $product->weight * 14000 / 1000,
            'grand_total' => ($qty * $product->price) + ($product->weight * 14000 / 1000)
        ]);
        if ($cart->save()) {
            $cartDetail = CartDetail::where('cart_id', $cart->id)->where('product_id', $product_id)->first();
            $cartDetailQty = !empty($cartDetail->qty) ? $cartDetail->qty : 0;
            if(empty($cartDetail)){
                $cartDetail = new CartDetail;
            }
            $cartDetail->fill([
                'cart_id' => $cart->id,
                'product_id' => $product_id,
                'qty' => $qty + $cartDetailQty
            ]);
            if($cartDetail->save()){
                $response['success'] = true;
                $response['message'] = 'Product ' . $product->name . ' added to cart successfully!';

                return response($response);
            }else{
                $response['success'] = false;
                $response['message'] = 'Unknown Error!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);

        }
    }

    /**
     * Remove a product from cart
     *
     * @param $product_id
     * @url /cart/remove-product/{product_id}
     */
    public function removeProductFromCart($product_id){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();
        $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $product_id);
        $product = json_decode($theProduct->getBody());
        $cartDetail = CartDetail::where('cart_id', $cart->id)->where('product_id', $product_id)->delete();
        if($cartDetail){
            $response['success'] = true;
            $response['message'] = 'Product ' . $product->name . ' has been removed from cart';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Product Not Found!';

            return response($response);

        }
    }

    /**
     * Delete entire cart
     *
     * @param $request Request
     * @url /cart/delete/
     */
    public function delete(){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();

        $cartDetail = CartDetail::where('cart_id', $cart->id)->delete();

        if ($cartDetail) {
            if($cart->delete()){
                $response['success'] = true;
                $response['message'] = 'Cart deleted successfully!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Cart is empty!';

            return response($response);
        }
    }

    /**
     * Submit coupon in Cart
     *
     * @param $request Request
     * @url /cart/submit-coupon/
     */
    public function submitCoupon(Request $request){
        if ($request->has('code_name')) {
            $theCoupon = $this->client->get(self::COUPON_ENDPOINT . $request->input('code_name'));
            $coupon = json_decode($theCoupon->getBody());
            $today = new DateTime();
            $valid_start = new DateTime($coupon->valid_start);
            $valid_till = new DateTime($coupon->valid_till);
            // check range date of coupon validity and also the quantity
            if(($today >= $valid_start) && ($today <= $valid_till) && $coupon->qty > 0){
                $refreshCart = $this->refreshCart($coupon);
                $response['success'] = true;
                $response['message'] = 'Coupon applied successfully!';
                $response['cart'] = $refreshCart;

                return response($response);
            }else{
                $response['success'] = false;
                $response['message'] = 'Coupon not valid!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Coupon must be filled!';

            return response($response);
        }
    }

    /**
     * Refresh Cart after Submitting Coupon
     *
     * @param $coupon
     */
    public function refreshCart($coupon){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();
        if ($cart) {
            $total_amount = 0;
            $weight = 0;
            $cartDetail = CartDetail::where('cart_id', $cart->id)->get();

            foreach ($cartDetail as $key => $value) {
                // @TODO this should have to implements Batch, right now it's use one by one Product Microservice call to ease my work 
                $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $value->product_id);
                $product = json_decode($theProduct->getBody());
                $value->setAttribute('product_name', $product->name);
                $value->setAttribute('product_weight', $product->weight);
                $total_amount += $product->price * $value->qty;
                $weight += $product->weight * $value->qty;
            }
            //using gram, so it must divides by 1000, and 14000 is the average shipment fee from Jogja to Jakarta per kg
            $cart->shipment_fee = $weight * 14000 / 1000;
            $cart->total_amount = $total_amount;
            // include coupon discount
            $cart->grand_total = $cart->shipment_fee + $cart->total_amount - $coupon->discount;
            //save the coupon detail in cart
            $cart->coupon_id = $coupon->id;
            $cart->coupon_code_name = $coupon->code_name;
            $cart->coupon_discount = $coupon->discount;

            if ($cart->save()) {
                $cart->items = !$cartDetail->isEmpty() ? $cartDetail : array('Let`s Start Shopping');

                return $cart;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Remove coupon from Cart
     *
     * @url /cart/remove-coupon/
     */
    public function removeCoupon(){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();

        if ($cart) {
            $total_amount = 0;
            $weight = 0;
            $cartDetail = CartDetail::where('cart_id', $cart->id)->get();

            foreach ($cartDetail as $key => $value) {
                // @TODO this should have to implements Batch, right now it's use one by one call Product Microservice to ease my work 
                $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $value->product_id);
                $product = json_decode($theProduct->getBody());
                $value->setAttribute('product_name', $product->name);
                $value->setAttribute('product_weight', $product->weight);
                $total_amount += $product->price * $value->qty;
                $weight += $product->weight * $value->qty;
            }
            //remove coupon
            $cart->coupon_id = null;
            $cart->coupon_code_name = null;
            $cart->coupon_discount = null;
            //using gram, so it must divides by 1000, and 14000 is the average shipment fee from Jogja to Jakarta per kg
            $cart->shipment_fee = $weight * 14000 / 1000;
            $cart->total_amount = $total_amount;
            $cart->grand_total = $cart->shipment_fee + $cart->total_amount;

            if ($cart->save()) {
                $cart->items = !$cartDetail->isEmpty() ? $cartDetail : array('Let`s Start Shopping');
                $response['success'] = true;
                $response['message'] = 'Coupon removed successfully!';
                $response['cart'] = $cart;

                return response($response);
            }else{
                $response['success'] = false;
                $response['message'] = 'Unknown Error!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Your cart is empty! Check our products and happy shopping :)';

            return response($response);
        }
    }


    /**
     * get Cart to Json
     *
     * url: /cart/get-cart-json
     */
    public function getCartJson(){
        $cart = Cart::where('customer_id', $this->loggedUser->message->id)->first();

        if ($cart) {
            $total_amount = 0;
            $weight = 0;
            $cartDetail = CartDetail::where('cart_id', $cart->id)->get();

            foreach ($cartDetail as $key => $value) {
                // @TODO this should have to implements Batch, right now it's use one by one call Product Microservice to ease my work 
                $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $value->product_id);
                $product = json_decode($theProduct->getBody());
                $value->setAttribute('product_name', $product->name);
                $value->setAttribute('product_weight', $product->weight);
                $total_amount += $product->price * $value->qty;
                $weight += $product->weight * $value->qty;
            }
            //using gram, so it must divides by 1000, and 14000 is the average shipment fee from Jogja to Jakarta per kg
            $cart->shipment_fee = $weight * 14000 / 1000;
            $cart->total_amount = $total_amount;
            $cart->grand_total = $cart->shipment_fee + $cart->total_amount;
            if ($cart->coupon_id) {
                // include coupon discount
                $cart->grand_total = $cart->shipment_fee + $cart->total_amount - $cart->coupon_discount;
                //save the coupon detail in cart
                $cart->coupon_id = $cart->coupon_id;
                $cart->coupon_code_name = $cart->coupon_code_name;
                $cart->coupon_discount = $cart->coupon_discount;
            }

            if ($cart->save()) {
                $cart->items = !$cartDetail->isEmpty() ? $cartDetail : array('Let`s Start Shopping');
                
                return response()->json(
                    $cart
                );
            }else{
                $response['success'] = false;
                $response['message'] = 'Unknown Error!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Your cart is empty! Check our products and happy shopping :)';

            return response($response);
        }
    }


    /**
     * Truncate cart and cart_detail
     *
     * @param $request Request
     * @url /cart/truncate/
     */
    public function truncate(){
        $cart = Cart::truncate();

        $cartDetail = CartDetail::truncate();

        if ($cart) {
                $response['success'] = true;
                $response['message'] = 'Cart truncated successfully!';

                return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Cart is empty!';

            return response($response);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\OrderDetail;
use App\Shipment;
use GuzzleHttp\Client;

class OrderController extends Controller
{
    const PRODUCT_ENDPOINT = 'http://localhost:3131/api/v1/product/';
    const USER_ENDPOINT = 'http://localhost:3132/api/v1/user/';
    const CART_ENDPOINT = 'http://localhost:3133/api/v1/cart/';
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

        $theUser = $this->client->get(self::USER_ENDPOINT . 'api/' . $this->api_token);
        $this->loggedUser = json_decode($theUser->getBody());
        //kick out the illegal user
        if($this->loggedUser->message == 'API KEY Invalid!' || $this->loggedUser->message == 'Please login first!'){
            die($this->loggedUser->message);
        }
    }

    /**
     * Show all Orders
     *
     * @param $request Request
     * @url /order
     */
    public function index(Request $request){
        if($this->loggedUser->message->role != 'admin') {
            $response['success'] = false;
            $response['message'] = 'Restricted Area!';

            return response($response);
        }
        $order = Order::all();

        if (count($order) > 0) {
            $response['success'] = true;
            $response['message'] = $order;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'No Orders Found!';

            return response($response);

        }
    }

    /**
     * Show Detail Order
     *
     * @param $id
     * @url /order/{$id}
     */
    public function detail($id){
        if($this->loggedUser->message->role != 'admin') {
            $response['success'] = false;
            $response['message'] = 'Restricted Area!';

            return response($response);
        }
        $order = Order::find($id);
        $orderDetail = OrderDetail::where('order_id', $id)->get();
        $order->details = !$orderDetail->isEmpty() ? $orderDetail : array('Let`s Start Shopping');


        if (count($orderDetail) > 0) {
            $response['success'] = true;
            $response['message'] = $order;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'No Orders Found!';

            return response($response);

        }
    }


    /**
     * Show Detail Order
     *
     * @param $request Request
     * @url /track
     */
    public function trackOrder(Request $request){
        if ($request->has('order_no')) {
            $order = Order::where('order_no', $request->input('order_no'))->first();

            if ($order) {
                $orderDetail = OrderDetail::where('order_id', $order->id)->get();
                $order->details = !$orderDetail->isEmpty() ? $orderDetail : array('Let`s Start Shopping');

                if (count($orderDetail) > 0) {
                    $response['success'] = true;
                    $response['message'] = $order;

                    return response($response);
                }else{
                    $response['success'] = false;
                    $response['message'] = 'No Orders Found!';

                    return response($response);

                }
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'No Orders Found!';

            return $response;
        }
    }

    /**
     * Checkout Cart
     *
     * @param $request Request
     * @url /order/checkout/
     * 
     */
    public function checkout(Request $request){
        try {
            //init DB Transaction to rollback the failure while inserting to database
            DB::beginTransaction();
            
            $theCart = $this->client->get(self::CART_ENDPOINT . 'get-cart-json');
            $cart = json_decode($theCart->getBody());
            if (!empty($cart) && $request->has('shipping_name')) {
                $order = new Order;
                $order->fill([
                    'order_no' => date('ymd') . time(),
                    'coupon_id' => $cart->coupon_id,
                    'coupon_code_name' => $cart->coupon_code_name,
                    'coupon_discount' => $cart->coupon_discount,
                    'customer_id' => $cart->customer_id,
                    'customer_name' => $cart->customer_name,
                    'customer_email' => $cart->customer_email,
                    'total_amount' => $cart->total_amount,
                    'shipment_fee' => $cart->shipment_fee,
                    'grand_total' => $cart->grand_total,
                    'shipping_name' => $request->input('shipping_name'),
                    'shipping_phone' => $request->input('shipping_phone'),
                    'shipping_email' =>  $request->input('shipping_email'),
                    'shipping_address' => $request->input('shipping_address'),
                ]);

                if ($order->save()) {

                    //reduce coupon qty if the customer uses coupon
                    if(!empty($cart->coupon_id)){
                        $theCoupon = $this->client->get(self::COUPON_ENDPOINT . $cart->coupon_code_name);
                        $coupon = json_decode($theCoupon->getBody());
                        $newCouponQty = $coupon->qty - 1;
                        $updateCouponQty = $this->client->get(self::COUPON_ENDPOINT . 'update-qty/' . $cart->coupon_id . '/' . $newCouponQty);
                        $statusUpdateCoupon = json_decode($updateCouponQty->getBody());
                        if(!$statusUpdateCoupon->success){
                            DB::rollBack();
                            $response['success'] = false;
                            $response['message'] = 'Cannot Update Coupon Quantity!';
                            
                            return response($response);
                        }
                    }

                    foreach ($cart->items as $key => $value) {
                        $theProduct = $this->client->get(self::PRODUCT_ENDPOINT . $value->product_id);
                        $product = json_decode($theProduct->getBody());

                        //reduce product stock
                        $newStock = $product->stock - $value->qty;
                        $updateStockProduct = $this->client->get(self::PRODUCT_ENDPOINT . 'update-stock/' . $value->product_id . '/' . $newStock);
                        $statusUpdateProduct = json_decode($updateStockProduct->getBody());

                        if(!$statusUpdateProduct->success){
                            DB::rollBack();
                            $response['success'] = false;
                            $response['message'] = 'Cannot Update Product Stock!';
                            
                            return response($response);
                        }
                        
                        $cartDetail = new OrderDetail;
                        $cartDetail->fill([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_price' => $product->price,
                            'product_weight' => $product->weight,
                            'qty' => $value->qty,
                            'subtotal' => $product->price * $value->qty,
                        ]);
                        if (!$cartDetail->save()) {

                            DB::rollBack();
                            $response['success'] = false;
                            $response['message'] = 'Cannot save to DB!';
                            
                            return response($response);
                        }
                    }

                    //truncate cart and cart detail
                    $theCartTruncate = $this->client->get(self::CART_ENDPOINT . 'truncate');
                    $cartTruncate = json_decode($theCartTruncate->getBody());
                    
                    if($cartTruncate->success){
                        DB::commit();

                        $response['success'] = true;
                        $response['message'] = 'Checkout order successfully!';
                    }else{
                        DB::rollBack();

                        $response['success'] = false;
                        $response['message'] = 'Cannot save to DB!';
                    }
                }else{
                    DB::rollBack();

                    $response['success'] = false;
                    $response['message'] = 'Cannot save to DB!';
                }
            }else{
                $response['success'] = false;
                $response['message'] = 'Shipping details is Required!';
            }
            return response($response);
        } catch (Exception $e) {
            DB::rollBack();

            $response['success'] = false;
            $response['message'] = 'Cannot save to DB!';

            return response($response);
        }
    }

    /**
     * Payment Confirmation
     *
     * @param $request Request
     * @url /order/payment-confirm/
     * 
     */
    public function paymentConfirm(Request $request){
        if ($request->has('order_no')) {
            $order = Order::where('order_no', $request->input('order_no'))->first();

            if ($order) {
                $order->paid_grand_total = $request->input('paid_amount');
                $order->paid_date = $request->input('paid_date');
                $order->payment_proof = $request->input('payment_proof');
                $order->status = 'payment_confirmed';

                if ($order->save()) {
                    $response['success'] = false;
                    $response['message'] = 'Thank you for your payment confirmation.';

                    return $response;
                }else{
                    $response['success'] = false;
                    $response['message'] = 'Cannot save to DB!';

                    return $response;
                }
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'All fields are required!';

            return $response;
        }
    }

    /**
     * Validate Order
     *
     * @param $request Request
     * @url /order/validate/
     * 
     */
    public function validateOrder(Request $request){
        if($this->loggedUser->message->role != 'admin') {
            $response['success'] = false;
            $response['message'] = 'Restricted Area!';

            return response($response);
        }
        if ($request->has('order_no')) {
            $order = Order::where('order_no', $request->input('order_no'))->first();
            if (!$order) {
                $response['success'] = false;
                $response['message'] = 'Can not find Order No. ' . $request->input('order_no');

                return $response;
            }
            if ($order->paid_grand_total == $order->grand_total && !empty($order->paid_date) && !empty($order->payment_proof)) {
                $order->status = 'processed';

                if ($order->save()) {
                    $response['success'] = true;
                    $response['message'] = 'The order is valid. You must provide the Shipping ID A.S.A.P';

                    return $response;
                }else{
                    $response['success'] = false;
                    $response['message'] = 'Cannot save to DB!';

                    return $response;
                }
            }else{
                $order->status = 'rejected';

                if ($order->save()) {
                    $response['success'] = false;
                    $response['message'] = 'The order is invalid. You do not provide the Shipping ID';

                    return $response;
                }else{
                    $response['success'] = false;
                    $response['message'] = 'Cannot save to DB!';

                    return $response;
                }
            }

        }else{
            $response['success'] = false;
            $response['message'] = 'All fields are required!';

            return $response;
        }
    }

    /**
     * Shipping The Order
     *
     * @param $request Request
     * @url /order/shipment/
     * 
     */
    public function shipmentOrder(Request $request){
        if($this->loggedUser->message->role != 'admin') {
            $response['success'] = false;
            $response['message'] = 'Restricted Area!';

            return response($response);
        }
        if ($request->has('order_no')) {
            $order = Order::where('order_no', $request->input('order_no'))->first();
            if (!$order) {
                $response['success'] = false;
                $response['message'] = 'Can not find Order No. ' . $request->input('order_no');

                return $response;
            }
            $order->shipping_ID = $request->input('shipping_ID');
            $order->status = 'shipped';

            if ($order->save()) {
                $shipment = new Shipment;
                $shipment->order_no = $order->order_no;
                $shipment->shipping_ID = $order->shipping_ID;

                if ($shipment->save()) {
                    $response['success'] = true;
                    $response['message'] = 'Shipping ID has been provided. Order on the fly to the customer.';

                    return $response;
                }
            }else{
                $response['success'] = false;
                $response['message'] = 'Cannot save to DB!';

                return $response;
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'All fields are required!';

            return $response;
        }
    }

    /**
     * Track Shipping ID
     *
     * @param $request Request
     * @url /track-shipment
     */
    public function trackShipment(Request $request){
        if ($request->has('order_no')) {
            $shipment = Shipment::where('order_no', $request->input('order_no'))->first();

            if ($shipment) {
                    $response['success'] = true;
                    $response['message'] = $shipment;

                    return response($response);
            }else{
                $response['success'] = false;
                $response['message'] = 'The Shipping ID is invalid!';

                return response($response);

            }
        }else{
            $response['success'] = false;
            $response['message'] = 'No Orders Found!';

            return $response;
        }
    }
}

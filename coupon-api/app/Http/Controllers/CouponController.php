<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coupon;
use GuzzleHttp\Client;

class CouponController extends Controller
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

        //kick out the illegal user
        $theUser = $this->client->get(self::USER_ENDPOINT . 'api/' . $this->api_token);
        $this->loggedUser = json_decode($theUser->getBody());
        if($this->loggedUser->message == 'API KEY Invalid!' || $this->loggedUser->message == 'Please login first!'){
            die($this->loggedUser->message);
        }
    }

    /**
     * Show all Coupons
     *
     * @param $request Request
     */
    public function index(Request $request){
        $coupons = Coupon::all();

        if (count($coupons) > 0) {
            $response['success'] = true;
            $response['message'] = $coupons;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'No Coupons Found!';

            return response($response);

        }
    }

    /**
     * Insert new coupon
     *
     * @param $request Request
     * @url /coupon
     */
    public function create(Request $request){
        $coupon = new Coupon;
        $coupon->fill([
            'code_name' => $request->input('code_name'),
            'desc' => $request->input('desc'),
            'qty' => $request->input('qty'),
            'valid_start' => $request->input('valid_start'),
            'valid_till' => $request->input('valid_till'),
            'discount' => $request->input('discount')
        ]);
        if ($coupon->save()) {
            $response['success'] = true;
            $response['message'] = 'Coupon added successfully!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);

        }
    }

    /**
     * Get coupon, find by ID
     *
     * @param $request Request, $id
     * @url /coupon/{id}
     */
    public function getCoupon(Request $request, $id){
        $coupon = Coupon::find($id);
        if($coupon){
            $response['success'] = true;
            $response['message'] = $coupon;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Coupon Not Found!';

            return response($response);

        }
    }

    /**
     * Get coupon, find by code_name
     *
     * @param $request Request, $id
     * @url /coupon/{code_name}
     */
    public function getCouponCodeName(Request $request, $code_name){
        $coupon = Coupon::where('code_name', $code_name)->first();
        if($coupon){
            // $response['success'] = true;
            // $response['message'] = $coupon;

            // return response($response);
            return response()->json(
                $coupon
            );
        }else{
            $response['success'] = false;
            $response['message'] = 'Coupon Not Found!';

            return response($response);

        }
    }

    /**
     * Update coupon
     *
     * @param $request Request, $id
     * @url /coupon/update/{id}
     */
    public function update(Request $request, $id){
        if ($request->has('code_name')) {
            $coupon = Coupon::find($id);
            $coupon->code_name = $request->input('code_name');
            $coupon->desc = $request->input('desc');
            $coupon->qty = $request->input('qty');
            $coupon->valid_start = $request->input('valid_start');
            $coupon->valid_till = $request->input('valid_till');
            $coupon->discount = $request->input('discount');
            if ($coupon->save()) {
                $response['success'] = true;
                $response['message'] = 'Coupon updated successfully!';

                return response($response);
            }else{
                $response['success'] = false;
                $response['message'] = 'Unknown Error!';

                return response($response);
            }
        }else{
            $response['success'] = false;
            $response['message'] = 'Nothing Changed!';

            return response($response);
        }
    }

    /**
     * Update coupon qty
     *
     * @param $coupon_id, $new_qty
     * @url /coupon/update-qty/{coupon_id}/{new_qty}
     */
    public function updateQty($coupon_id, $new_qty){
        $coupon = Coupon::find($coupon_id);
        $coupon->qty = $new_qty;
        if ($coupon->save()) {
            $response['success'] = true;
            $response['message'] = 'Coupon updated successfully!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);
        }
    }

    /**
     * Delete coupon
     *
     * @param $request Request, $id
     * @url /coupon/delete/{id}
     */
    public function delete(Request $request, $id){
        $coupon = Coupon::find($id);
        if(!$coupon){
            $response['success'] = false;
            $response['message'] = 'Coupon Not Found!';

            return response($response);
        }
        if ($coupon->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Coupon deleted successfully!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use GuzzleHttp\Client;

class ProductController extends Controller
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
     * Show all Products
     *
     * @param $request Request
     */
    public function index(Request $request){
        $products = Product::where('sold', '0')->get();

        if (count($products) > 0) {
            $response['success'] = true;
            $response['message'] = $products;

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'No Products Found!';

            return response($response);

        }
    }

    /**
     * Insert new product
     *
     * @param $request Request
     * @url /product
     */
    public function create(Request $request){
        $product = new Product;
        $product->fill([
            'name' => $request->input('name'),
            'desc' => $request->input('desc'),
            'stock' => $request->input('stock'),
            'price' => $request->input('price'),
            'weight' => $request->input('weight')
        ]);
        if ($product->save()) {
            $response['success'] = true;
            $response['message'] = 'Product added successfully!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);

        }
    }

    /**
     * Get product, find by ID
     *
     * @param $request Request, $id
     * @url /product/{id}
     */
    public function getProduct(Request $request, $id){
        $product = Product::find($id);
        if($product){
            // $response['success'] = true;
            // $response['message'] = $product;

            // return response($response);
            return response()->json(
                $product
            );
        }else{
            $response['success'] = false;
            $response['message'] = 'Product Not Found!';

            return response($response);

        }
    }

    /**
     * Update product
     *
     * @param $request Request, $id
     * @url /product/update/{id}
     */
    public function update(Request $request, $id){
        if ($request->has('name')) {
            $product = Product::find($id);
            $product->name = $request->input('name');
            $product->desc = $request->input('desc');
            $product->stock = $request->input('stock');
            $product->price = $request->input('price');
            $product->weight = $request->input('weight');
            if ($product->save()) {
                $response['success'] = true;
                $response['message'] = 'Product updated successfully!';

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
     * Update product
     *
     * @param $product_id, $new_stock
     * @url /product/update-stock/{product_id}/{new_stock}
     */
    public function updateStock($product_id, $new_stock){
        $product = Product::find($product_id);
        $product->stock = $new_stock;
        if ($product->save()) {
            $response['success'] = true;
            $response['message'] = 'Product updated successfully!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);
        }
    }

    /**
     * Delete product
     *
     * @param $request Request, $id
     * @url /product/delete/{id}
     */
    public function delete(Request $request, $id){
        $product = Product::find($id);
        if(!$product){
            $response['success'] = false;
            $response['message'] = 'Product Not Found!';

            return response($response);
        }
        if ($product->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Product deleted successfully!';

            return response($response);
        }else{
            $response['success'] = false;
            $response['message'] = 'Unknown Error!';

            return response($response);
        }
    }
}

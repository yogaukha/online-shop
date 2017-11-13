<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Cart extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
    * Table database
    */
    protected $table = 'carts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coupon_id', 'coupon_code_name', 'coupon_discount',
        'customer_id', 'customer_name', 'customer_email',
        'total_amount', 'shipment_fee', 'grand_total'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

    /**
     * Cart has many cart detail
     *
     * Get the cart_detail for the Cart
     */
    public function cartDetail(){
        return $this->hasMany('App\CartDetail', 'cart_id');
    }
}

<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Order extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
    * Table database
    */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coupon_id', 'coupon_code_name', 'coupon_discount',
        'customer_id', 'customer_name', 'customer_email',
        'total_amount', 'shipment_fee', 'grand_total', 'paid_grand_total', 'payment_proof', 'paid_date',
        'shipping_name', 'shipping_phone', 'shipping_email', 'shipping_address', 'shipping_ID',
        'status', 'order_no'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}

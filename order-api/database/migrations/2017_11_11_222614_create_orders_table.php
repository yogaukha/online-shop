<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no')->unique();
            $table->integer('coupon_id')->nullable();
            $table->string('coupon_code_name')->nullable();
            $table->integer('coupon_discount')->nullable();
            $table->integer('customer_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->integer('total_amount');
            $table->integer('shipment_fee');
            $table->integer('grand_total');
            $table->integer('paid_grand_total')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->string('shipping_email');
            $table->string('shipping_address');
            $table->string('shipping_ID')->nullable();
            $table->enum('status', ['waiting_payment', 'payment_confirmed', 'processed', 'shipped', 'rejected'])->default('waiting_payment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

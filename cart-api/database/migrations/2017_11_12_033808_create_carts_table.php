<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coupon_id')->nullable();
            $table->string('coupon_code_name')->nullable();
            $table->integer('coupon_discount')->nullable();
            $table->integer('customer_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->integer('total_amount');
            $table->integer('shipment_fee');
            $table->integer('grand_total');
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
        Schema::dropIfExists('carts');
    }
}

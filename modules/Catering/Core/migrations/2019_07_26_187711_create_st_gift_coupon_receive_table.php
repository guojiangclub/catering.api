<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStGiftCouponReceiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('st_gift_coupon_receive', function (Blueprint $table) {

            $table->increments('id');
            $table->string('origin_type');
            $table->integer('origin_id');
            $table->integer('user_id');
            $table->integer('discount_id');
            $table->integer('coupon_id');
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
        Schema::drop('st_gift_coupon_receive');
    }
}

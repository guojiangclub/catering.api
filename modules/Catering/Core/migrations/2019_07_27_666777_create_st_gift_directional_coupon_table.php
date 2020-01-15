<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStGiftDirectionalCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('st_gift_directional_coupon', function (Blueprint $table) {

            $table->increments('id');

            $table->string('directional_type')->nullable();  //mobile手机号  custom自定义

            $table->string('name')->nullable();                 //活动名称

            $table->tinyInteger('status')->default(1); 	 //状态：1 有效 ，0 失效

            $table->text('mobile')->nullable();                 //手机号

            $table->string('group_id')->nullable();                 //会员等级

            $table->string('n_day_buy')->nullable();                 //N天内有购买

            $table->string('n_day_no_buy')->nullable();                 //N天内有无购买

            $table->string('buy_num_above')->nullable();                 //累计购物次数大于

            $table->string('buy_num_below')->nullable();                 //累计购物次数小于

            $table->decimal('buy_price_above',15,2)->nullable();//购买商品价格大于

            $table->decimal('buy_price_below',15,2)->nullable();//购买商品价格小于

            $table->string('number')->nullable();                        //预计发送人数

            $table->integer('coupon_id')->default(0);                    //优惠券ID

            $table->nullableTimestamps();

            $table->softDeletes();

            $table->engine = 'InnoDB';

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('el_gift_directional_coupon');
    }
}

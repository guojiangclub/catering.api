<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStCouponCenterTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('st_coupon_center', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title')->comment('活动名称');
			$table->string('activity_banner')->comment('活动banner');
			$table->timestamp('starts_at')->nullable()->comment('开始时间');
			$table->timestamp('ends_at')->nullable()->comment('截止时间');
			$table->tinyInteger('status')->default(1)->comment('状态');
			$table->timestamps();
		});

		Schema::create('st_coupon_center_item', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('coupon_center_id')->comment('活动id');
			$table->integer('discount_id')->comment('优惠券id');
			$table->string('code')->comment('兑换码');
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
		Schema::dropIfExists('st_coupon_center');
		Schema::dropIfExists('st_coupon_center_item');
	}
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStOrderRefundTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('st_order_refund', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('order_id');
			$table->integer('clerk_id');
			$table->integer('user_id');
			$table->string('refund_type')->comment('退款类型');
			$table->string('refund_no')->nullable()->comment('退款订单号');
			$table->string('refundTargetOrderId')->nullable();
			$table->integer('refund_amount')->default(0)->comment('退款金额');
			$table->string('refundFundsDesc')->nullable();
			$table->string('targetSys')->nullable();
			$table->string('bankInfo')->nullable();
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
		Schema::dropIfExists('st_order_refund');
	}
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'payment')) {
			Schema::create($prefix . 'payment', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('order_id')->unsigned()->comment('关联的订单号');
				$table->integer('method_id')->unsigned()->nullable()->comment('使用的支付方式,暂时不使用');
				$table->string('pingxx_no')->nullable();
				$table->string('channel')->comment('支付渠道');
				$table->string('channel_no')->nullable();
				$table->integer('amount')->comment('本次支付的金额');
				$table->string('status');
				$table->text('details')->nullable()->comment('存储json meta 数据');
				$table->timestamp('paid_at')->nullable()->comment('支付时间');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'payment_method')) {
			Schema::create($prefix . 'payment_method', function (Blueprint $table) {
				$table->increments('id');
				$table->string('code');
				$table->string('name');
				$table->boolean('is_enabled')->default(true);
				$table->text('description')->nullable();
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'merchant_pay')) {
			Schema::create($prefix . 'merchant_pay', function (Blueprint $table) {
				$table->increments('id');
				$table->string('origin_type')->comment('打款类型：REFUND 退款;COMMISSION 分销佣金');
				$table->integer('origin_id');
				$table->string('channel')->default('wechat')->comment('打款渠道：wechat 微信； alipay 支付宝');
				$table->integer('channel_id')->default(0)->comment('如果是REFUND，记录el_refund_amount 的ID');
				$table->string('partner_trade_no')->comment('打款编号');
				$table->string('payment_no')->nullable()->comment('交易流水号');
				$table->integer('amount')->comment('金额');
				$table->string('status')->comment('打款状态:SUCCESS FAIL');
				$table->string('error_code')->nullable()->comment('失败状态码：NAME_MISMATCH');
				$table->string('err_code_des')->nullable()->comment('失败描述：真实姓名不一致');
				$table->dateTime('payment_time')->nullable()->comment('成功打款时间');
				$table->integer('user_id')->comment('用户ID');
				$table->integer('admin_id')->comment('操作人ID');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'payment_log')) {
			Schema::create($prefix . 'payment_log', function (Blueprint $table) {
				$table->increments('id');
				$table->string('action')->comment('create_charge 创建支付请求；result_pay 支付之后');
				$table->dateTime('operate_time')->comment('提交时间/支付时间');
				$table->string('order_no')->nullable()->comment('订单号');
				$table->string('transcation_order_no')->nullable()->comment('提交给微信的新的订单号');
				$table->string('transcation_no')->nullable()->comment('交易流水号');
				$table->integer('amount')->default(0)->comment('订单金额');
				$table->string('channel')->nullable()->comment('支付渠道 wx_pub_qr,wx_pub,wx_lite,alipay');
				$table->string('type')->nullable()->comment('订单类型：order，activity，recharge');
				$table->string('status')->nullable()->comment('状态：state，success，failed');
				$table->integer('user_id')->default(0)->comment('用户ID'); //
				$table->mediumText('meta')->nullable()->comment('记录微信、支付宝之后成功之后返回的所有数据');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'payment_refund_log')) {
			Schema::create($prefix . 'payment_refund_log', function (Blueprint $table) {
				$table->increments('id');
				$table->string('action')->comment('create_refund 创建退款请求；query_refund 查询退款');
				$table->dateTime('operate_time')->comment('提交时间');
				$table->string('refund_no')->nullable()->comment('退款编号');
				$table->string('order_no')->nullable()->comment('订单编号');
				$table->string('refund_id')->nullable()->comment('交易流水号');
				$table->integer('amount')->default(0)->comment('退款金额');
				$table->string('type')->nullable()->comment('订单类型：order，activity，recharge');
				$table->string('channel')->nullable()->comment('支付渠道 wx_pub_qr,wx_pub,wx_lite,alipay');
				$table->string('status')->nullable()->comment('状态：state，success，failed');
				$table->mediumText('meta')->nullable()->comment('记录微信、支付宝退款提交之后返回的所有数据');
				$table->timestamps();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		Schema::dropIfExists($prefix . 'payment');
		Schema::dropIfExists($prefix . 'payment_method');
		Schema::dropIfExists($prefix . 'merchant_pay');
		Schema::dropIfExists($prefix . 'payment_log');
		Schema::dropIfExists($prefix . 'payment_refund_log');
	}
}

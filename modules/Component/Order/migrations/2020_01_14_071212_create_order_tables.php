<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'order')) {
			Schema::create($prefix . 'order', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id')->comment('用户id');
				$table->string('order_no')->comment('订单编号');
				$table->integer('count')->unsigned()->default(0)->comment('商品总数');
				$table->integer('status')->unsigned()->default(0)->comment('订单状态：1生成订单,2支付订单,3取消订单,4作废订单,5完成订单,6退款');
				$table->string('produce_status')->nullable()->comment('生产状态，用于定制化产品');
				$table->string('pay_type')->nullable()->comment('支付方式 包含支付宝，微信，余额支付');
				$table->integer('pay_status')->default(0)->comment('支付状态：0未支付,1已支付');
				$table->integer('distribution_status')->default(0)->comment('发货状态：0未发货，1已发货');
				$table->integer('distribution')->nullable()->comment('配送方式ID');
				$table->string('coupon_item')->nullable()->comment('使用的优惠券编号');
				$table->integer('items_total')->comment('商品总金额');
				$table->integer('adjustments_total')->default(0)->comment('优惠金额，负数，包含了促销和优惠券以及其他优惠的总金额,默认为零因为可能没有优惠活动');
				$table->integer('payable_freight')->default(0)->comment('应付运费金额');
				$table->integer('real_freight')->default(0)->comment('实付运费金额');
				$table->integer('total')->comment('订单总金额:  items_total+adjustments_total+real_freight');
				$table->decimal('redeem_point')->default(0);
				$table->string('accept_name')->nullable()->comment('收货人姓名');
				$table->string('mobile')->nullable()->comment('电话号码');
				$table->string('address')->nullable();
				$table->string('address_name')->nullable()->comment('备用:收货地//详细地址址省市区名称');
				$table->timestamp('pay_time')->nullable()->comment('付款时间');
				$table->timestamp('send_time')->nullable()->comment('发货时间');
				$table->timestamp('completion_time')->nullable()->comment('订单完成时间');
				$table->timestamp('accept_time')->nullable()->comment('客户收货时间');
				$table->integer('point')->unsigned()->nullable()->comment('增加的积分');
				$table->string('message')->nullable()->comment('用户留言');
				$table->integer('type')->default(0)->comment('是否线上订单 0线上订单 1线下订单');
				$table->string('note')->nullable()->comment('管理员备注');
				$table->string('source')->nullable()->comment('订单来源:wechat 微信；pc PC商城；weprogram 小程序；mobile 手机浏览器');
				$table->timestamp('submit_time')->nullable();
				$table->string('cancel_reason')->nullable()->comment('生产状态，用于定制化产品');
				$table->tinyInteger('is_remind')->default(0)->nullable()->comment('是否发送模板消息');
				$table->string('channel')->default('ec')->comment('ec:电商订单；shop：门店订单；integral :积分订单');
				$table->integer('channel_id')->default(0)->comment('如果channel=shop，该字段表示门店ID');
				$table->timestamps();
				$table->softDeletes();

				$table->unique('order_no');
			});
		}

		if (!Schema::hasTable($prefix . 'order_item')) {
			Schema::create($prefix . 'order_item', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('order_id');
				$table->integer('item_id');
				$table->string('item_name');
				$table->string('type');
				$table->text('item_meta')->nullable();
				$table->integer('quantity')->unsigned()->comment('商品数量');
				$table->integer('unit_price')->comment('商品单价');
				$table->integer('units_total')->unsigned()->comment('商品总价 = unit_price * quantity');
				$table->integer('adjustments_total')->default(0)->nullable()->comment('总的优惠金额');
				$table->integer('item_discount')->nullable()->default(0)->comment('订单商品中的优惠金额');
				$table->integer('divide_order_discount')->nullable()->default(0)->comment('订单折扣平摊到该商品上的金额');
				$table->decimal('use_point', 8, 2)->nullable()->default(0);
				$table->integer('total')->comment('unitPrice * quantity - adjustmentsTotal');
				$table->integer('supplier_id')->default(1)->comment('供应商ID');
				$table->integer('shipping_id')->default(0)->comment('发货ID');
				$table->tinyInteger('is_send')->default(0)->comment('是否发货');
				$table->tinyInteger('is_commented')->nulable()->default(0);
				$table->tinyInteger('status')->default(1)->comment('item状态：1 正常；2 取消');
				$table->timestamps();
				$table->softDeletes();

				$table->index('order_id', 'order_item_order_id');
			});
		}

		if (!Schema::hasTable($prefix . 'order_adjustment')) {
			Schema::create($prefix . 'order_adjustment', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('order_id')->nullable();
				$table->integer('order_item_id')->nullable();
				$table->integer('order_item_unit_id')->nullable();
				$table->string('type')->comment('优惠对象，订单, 商品，运费等');
				$table->string('label')->nullable()->comment('文案描述："9折"');
				$table->integer('amount')->default(0)->comment('优惠金额，统一用分来表示');
				$table->string('origin_type')->nullable()->comment('优惠类型  discount, coupon ,membership,vip');
				$table->integer('origin_id')->default(0)->comment('优惠券ID或者discount ID,或者用户组group id');

				$table->timestamps();
				$table->softDeletes();
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

		Schema::dropIfExists($prefix . 'order');
		Schema::dropIfExists($prefix . 'order_item');
		Schema::dropIfExists($prefix . 'order_adjustment');
	}
}

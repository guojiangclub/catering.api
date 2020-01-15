<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'point')) {
			Schema::create($prefix . 'point', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id')->unsigned();
				//type:存在多种积分时用于分类
				$table->string('type')->default('default');
				//  order_item:     订单商品获得积分
				//  order_discount: 订单折扣使用积分
				//  order_canceled: 取消订单返还积分
				//  goods:          线下购物获得积分(TNF导入数据)
				$table->string('action')->comment('action:产生积分变化的动作，用于查询');
				$table->string('note')->comment('积分变化的提示信息，用于前台显示');
				$table->decimal('value', 10, 2)->default(0)->comment('积分变化数值，可为负数');
				$table->integer('valid_time')->default(0)->comment('有效期(天)，0为永久有效');
				$table->integer('item_id')->default(0)->comment('积分变化动作对应表的id');
				$table->string('item_type')->nullable();
				$table->integer('status')->default(1);
				$table->timestamps();
				$table->softDeletes();

				$table->index(['item_id', 'item_type']);
			});
		}

		if (!Schema::hasTable($prefix . 'point_goods')) {
			Schema::create($prefix . 'point_goods', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('item_id')->comment('商品ID');
				$table->tinyInteger('type')->comment('积分计算方式');
				$table->tinyInteger('status')->comment('开启状态');
				$table->integer('value')->comment('积分变化参数');
				$table->tinyInteger('can_use_point')->default(1)->comment('是否可使用积分抵扣');  //
				$table->string('note')->nullable()->comment('描述');

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
		Schema::dropIfExists($prefix . 'point');
		Schema::dropIfExists($prefix . 'point_goods');
	}
}

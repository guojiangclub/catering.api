<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'shipping_method')) {
			Schema::create($prefix . 'shipping_method', function (Blueprint $table) {
				$table->increments('id');
				$table->string('code');
				$table->string('name');
				$table->string('url')->nullable();
				$table->tinyInteger('is_enabled')->default(1);
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'shipping')) {
			Schema::create($prefix . 'shipping', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('method_id');
				$table->integer('order_id');
				$table->string('tracking')->nullable()->comment('快递单号');
				$table->timestamp('delivery_time')->nullable();
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'shipping_template')) {
			Schema::create($prefix . 'shipping_template', function (Blueprint $table) {
				$table->increments('id');
				$table->string('title')->comment('模板名称');
				$table->string('bear_freight')->default('CUSTOM')->comment('是否包邮：CUSTOM 自定义运费； SELLER 商家承担运费');
				$table->string('valuation')->default('NUMBER')->comment('计价方式:NUMBER 按件数；WEIGHT 按重量；VOLUME 按体积');
				$table->integer('status')->default(1)->comment('状态：1 有效；0 无效');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'shipping_template_extend')) {
			Schema::create($prefix . 'shipping_template_extend', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('shipping_template_id')->comment('模板ID');
				$table->integer('start_standard')->nullable()->comment('开始计价数量');
				$table->decimal('start_fee')->nullable()->comment('开始计价金额');
				$table->integer('add_standard')->nullable()->comment('每增加数量');
				$table->decimal('add_fee')->nullable()->comment('增加金额');
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

		Schema::dropIfExists($prefix . 'shipping_method');
		Schema::dropIfExists($prefix . 'shipping');
		Schema::dropIfExists($prefix . 'shipping_template');
		Schema::dropIfExists($prefix . 'shipping_template_extend');
	}
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'discount')) {
			Schema::create($prefix . 'discount', function (Blueprint $table) {
				$table->increments('id');
				$table->string('title')->comment('名称');
				$table->string('discount_img')->nullable()->comment('展示图片');
				$table->string('discount_bg_img')->nullable()->comment('背景图片');
				$table->string('label')->nullable()->comment('规则');
				$table->text('intro')->nullable()->comment('使用说明');
				$table->tinyInteger('exclusive')->nullable()->default(0);
				$table->integer('usage_limit')->nullable()->comment('发放总量');
				$table->integer('used')->default(0);
				$table->tinyInteger('coupon_based')->default(0);
				$table->string('code')->nullable();
				$table->integer('type')->default(0);
				$table->timestamp('starts_at')->nullable()->comment('领取有效期');
				$table->timestamp('ends_at')->nullable()->comment('领取有效期');
				$table->tinyInteger('status')->default(1);
				$table->timestamp('usestart_at')->nullable()->comment('使用开始时间');
				$table->timestamp('useend_at')->nullable()->comment('使用截止时间');
				$table->integer('per_usage_limit')->nullable()->default(0);
				$table->string('tags')->nullable()->comment('促销标签');
				$table->tinyInteger('is_open')->default(1);
				$table->string('url')->nullable();
				$table->string('channel', 10)->nullable()->default('ec');
				$table->tinyInteger('is_agent_share')->default(0);
				$table->string('effective_days')->default(0)->comment('有效天数');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'discount_action')) {
			Schema::create($prefix . 'discount_action', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('discount_id');
				$table->string('type');
				$table->mediumText('configuration')->nullable();
			});
		}

		if (!Schema::hasTable($prefix . 'discount_rule')) {
			Schema::create($prefix . 'discount_rule', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('discount_id');
				$table->string('type');
				$table->mediumText('configuration')->nullable();
			});
		}

		if (!Schema::hasTable($prefix . 'discount_coupon')) {
			Schema::create($prefix . 'discount_coupon', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('discount_id');
				$table->integer('user_id');
				$table->string('code')->nullable();
				$table->timestamp('used_at')->nullable();
				$table->timestamp('expires_at')->nullable();
				$table->string('channel', 10)->nullable()->default('ec');
				$table->integer('manager_id')->default(0)->comment('核销人');
				$table->string('coupon_use_code')->nullable()->comment('核销码');
				$table->timestamps();
				$table->softDeletes();

				$table->index('code');
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

		Schema::dropIfExists($prefix . 'discount');
		Schema::dropIfExists($prefix . 'discount_action');
		Schema::dropIfExists($prefix . 'discount_rule');
		Schema::dropIfExists($prefix . 'discount_coupon');
	}
}

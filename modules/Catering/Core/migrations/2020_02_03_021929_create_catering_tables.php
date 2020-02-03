<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCateringTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');
		if (!Schema::hasTable($prefix . 'gift_activity')) {
			Schema::create($prefix . 'gift_activity', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('活动名称');
				$table->string('title')->nullable()->comment('标题');
				$table->tinyInteger('status')->default(0);
				$table->integer('point')->default(0);
				$table->timestamp('starts_at')->nullable();
				$table->timestamp('ends_at')->nullable();
				$table->string('type', 10)->default('all')->comment('赠送方式');
				$table->string('activity_type')->default('gift_new_user')->comment('类型：gift_new_user 新人礼；gift_birthday 生日礼');

				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'gift_discount')) {
			Schema::create($prefix . 'gift_discount', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('gift_activity_id');
				$table->integer('discount_id');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'clerk')) {
			Schema::create($prefix . 'clerk', function (Blueprint $table) {
				$table->increments('id');
				$table->string('clerk_no')->nullable()->comment('店员工号');
				$table->string('name')->comment('姓名');
				$table->string('nick_name')->nullable()->comment('昵称');
				$table->string('avatar')->nullable()->comment('头像');
				$table->string('email')->nullable()->comment('邮箱');
				$table->string('mobile')->comment('手机号');
				$table->string('password')->comment('密码');
				$table->tinyInteger('status')->default(1)->comment('状态');
				$table->tinyInteger('is_clerk_owner')->default(0)->comment('是否为店长');
				$table->tinyInteger('receive_template_message')->default(0)->comment('接受统计模板消息');
				$table->string('openid')->nullable()->comment('公众号openid');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'order_refund')) {
			Schema::create($prefix . 'order_refund', function (Blueprint $table) {
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

		if (!Schema::hasTable($prefix . 'banners')) {
			Schema::create($prefix . 'banners', function (Blueprint $table) {
				$table->increments('id');
				$table->string('blank_url')->nullable()->comment('跳转连接');
				$table->string('blank_type')->default('self')->comment('跳转类型');
				$table->string('img')->comment('图片地址');
				$table->integer('sort')->default(99)->nullable()->comment('排序');
				$table->tinyInteger('status')->default(1)->comment('状态 1:启用 0:禁用');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'activity')) {
			Schema::create($prefix . 'activity', function (Blueprint $table) {
				$table->increments('id');
				$table->string('title')->comment('标题');
				$table->string('img_url')->comment('头图');
				$table->string('img')->comment('banner');
				$table->string('qr_code_url')->nullable()->comment('活动小程序码');
				$table->string('address')->comment('详细地址');
				$table->string('floor_no')->comment('所在楼层');
				$table->timestamp('started_at')->nullable()->comment('活动开始时间');
				$table->timestamp('ended_at')->nullable()->comment('活动结束时间');
				$table->longText('content')->comment('活动详情');
				$table->text('sub_activity_ids')->nullable()->comment('关联活动');
				$table->tinyInteger('status')->default(1)->comment('状态 1:启用 0:下架');
				$table->tinyInteger('is_recommended')->default(0)->comment('是否推荐 1:是 0:否');
				$table->tinyInteger('is_notice')->default(0)->comment('是否开启提醒 1:是 0:否');
				$table->unsignedInteger('collect_count')->default(0)->comment('收藏数量');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'gift_coupon_receive')) {
			Schema::create($prefix . 'gift_coupon_receive', function (Blueprint $table) {
				$table->increments('id');
				$table->string('origin_type');
				$table->integer('origin_id');
				$table->integer('user_id');
				$table->integer('discount_id');
				$table->integer('coupon_id');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'gift_directional_coupon')) {
			Schema::create($prefix . 'gift_directional_coupon', function (Blueprint $table) {
				$table->increments('id');
				$table->string('directional_type')->nullable()->comment('mobile手机号  custom自定义');
				$table->string('name')->nullable()->comment('活动名称');
				$table->tinyInteger('status')->default(1)->comment('状态：1 有效 ，0 失效');
				$table->text('mobile')->nullable()->comment('手机号');
				$table->string('group_id')->nullable()->comment('会员等级');
				$table->string('n_day_buy')->nullable()->comment('N天内有购买');
				$table->string('n_day_no_buy')->nullable()->comment('N天内有无购买');
				$table->string('buy_num_above')->nullable()->comment('累计购物次数大于');
				$table->string('buy_num_below')->nullable()->comment('累计购物次数小于');
				$table->decimal('buy_price_above', 15, 2)->nullable()->comment('购买商品价格大于');
				$table->decimal('buy_price_below', 15, 2)->nullable()->comment('购买商品价格小于');
				$table->string('number')->nullable()->comment('预计发送人数');
				$table->integer('coupon_id')->default(0)->comment('优惠券ID');
				$table->nullableTimestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'coupon_center')) {
			Schema::create($prefix . 'coupon_center', function (Blueprint $table) {
				$table->increments('id');
				$table->string('title')->comment('活动名称');
				$table->string('activity_banner')->comment('活动banner');
				$table->timestamp('starts_at')->nullable()->comment('开始时间');
				$table->timestamp('ends_at')->nullable()->comment('截止时间');
				$table->tinyInteger('status')->default(1)->comment('状态');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'coupon_center_item')) {
			Schema::create($prefix . 'coupon_center_item', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('coupon_center_id')->comment('活动id');
				$table->integer('discount_id')->comment('优惠券id');
				$table->string('code')->comment('兑换码');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'clerk_bind')) {
			Schema::create($prefix . 'clerk_bind', function (Blueprint $table) {
				$table->increments('id');
				$table->string('app_id');
				$table->tinyInteger('subscribe')->default(1)->comment('是否关注 1关注 0未关注');
				$table->string('openid')->nullable()->comment('公众号唯一标识');
				$table->string('nick_name')->nullable()->comment('用户昵称');
				$table->tinyInteger('sex')->nullable()->comment('用户性别');
				$table->string('city')->nullable()->comment('用户所在城市');
				$table->string('province')->nullable()->comment('用户所在省份');
				$table->string('country')->nullable()->comment('用户所在国家');
				$table->string('language')->nullable()->comment('用户的语言，简体中文为zh_CN');
				$table->string('headimgurl')->nullable()->comment('用户头像');
				$table->integer('subscribe_time')->default(0)->comment('用户关注时间');
				$table->string('unionid')->nullable()->comment('');
				$table->string('remark')->nullable()->comment('备注');
				$table->string('groupid')->nullable()->comment('用户所在的分组ID');
				$table->string('tagid_list')->nullable()->comment('用户被打上的标签ID列表');
				$table->string('subscribe_scene')->nullable()->comment('用户关注的渠道来源');
				$table->string('qr_scene')->nullable()->comment('二维码扫码场景');
				$table->string('qr_scene_str')->nullable()->comment('二维码扫码场景描述');
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
		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');

		Schema::dropIfExists($prefix . 'gift_activity');
		Schema::dropIfExists($prefix . 'gift_discount');
		Schema::dropIfExists($prefix . 'clerk');
		Schema::dropIfExists($prefix . 'order_refund');
		Schema::dropIfExists($prefix . 'banners');
		Schema::dropIfExists($prefix . 'activity');
		Schema::dropIfExists($prefix . 'gift_coupon_receive');
		Schema::dropIfExists($prefix . 'gift_directional_coupon');
		Schema::dropIfExists($prefix . 'coupon_center');
		Schema::dropIfExists($prefix . 'coupon_center_item');
		Schema::dropIfExists($prefix . 'clerk_bind');
	}
}

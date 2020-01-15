<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'user')) {
			Schema::create($prefix . 'user', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->unique()->nullable()->comment('姓名');
				$table->string('nick_name')->nullable()->comment('昵称');
				$table->integer('extend_id')->default(0);
				$table->string('email')->nullable()->comment('邮箱');
				$table->string('mobile')->unique()->nullable()->comment('手机号');
				$table->string('password')->nullable();
				$table->tinyInteger('status')->default(1);
				$table->string('confirmation_code')->nullable();
				$table->boolean('confirmed')->default(false);
				$table->string('sex')->nullable()->comment('性别');
				$table->string('avatar')->nullable()->comment('头像');
				$table->float('integral')->nullable()->default(0)->comment('用户总积分');
				$table->integer('current_exp')->nullable()->default(0);
				$table->integer('available_integral')->nullable()->default(0)->comment('用户现有积分');
				$table->decimal('discount', 8, 2)->nullable()->comment('用户余额');
				$table->string('city')->nullable();
				$table->string('education')->nullable();
				$table->string('birthday')->nullable()->comment('出生日期');
				$table->string('qq')->nullable();
				$table->string('card_limit')->nullable()->comment('会员等级时限 领取时间');
				$table->string('card_no')->nullable()->comment('会员卡号');
				$table->string('barcode_url')->nullable()->comment('条形码');
				$table->string('qr_code_url')->nullable()->comment('二维码');
				$table->integer('group_id')->nullable();
				$table->string('union_id')->nullable()->comment('微信unionid');

				$table->rememberToken();
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'user_bind')) {
			Schema::create($prefix . 'user_bind', function (Blueprint $table) {
				$table->increments('id');
				$table->string('type')->comment('qq, wechat, weibo,douban');
				$table->string('app_id')->nullable();
				$table->string('open_id');
				$table->integer('user_id')->nullable();
				$table->string('nick_name')->nullable();
				$table->string('name')->nullable();
				$table->string('province')->nullable();
				$table->string('email')->nullable();
				$table->string('avatar')->nullable();
				$table->string('token')->nullable();
				$table->string('refresh_token')->nullable();
				$table->timestamp('expired_at')->nullable();
				$table->string('unionid')->nullable();
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'user_group')) {
			Schema::create($prefix . 'user_group', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('名称');
				$table->integer('grade')->default(0)->comment('等级');
				$table->integer('ratio')->nullable()->default(0);
				$table->integer('discount')->nullable()->default(0);
				$table->integer('min')->nullable()->comment('最小经验');
				$table->integer('max')->nullable()->comment('最大经验');
				$table->string('pic')->nullable()->comment('用户组图片');
				$table->string('rights_ids')->nullable()->comment('会员等级权益ID，数组');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'user_login_log')) {
			Schema::create($prefix . 'user_login_log', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id');
				$table->string('ip')->nullable();
				$table->string('platform')->default('website')->comment('website:默认浏览器  wechat：微信环境 miniprogram：小程序');
				$table->timestamp('login_time');
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

		Schema::dropIfExists($prefix . 'user');
		Schema::dropIfExists($prefix . 'user_bind');
		Schema::dropIfExists($prefix . 'user_group');
		Schema::dropIfExists($prefix . 'user_login_log');
	}
}

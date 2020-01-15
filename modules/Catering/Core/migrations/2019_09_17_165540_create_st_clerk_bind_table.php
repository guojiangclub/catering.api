<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStClerkBindTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('st_clerk_bind', function (Blueprint $table) {
			$table->increments('id');
			$table->string('app_id');
			$table->integer('clerk_id')->nullable()->default(0);
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

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('st_clerk_bind');
	}
}

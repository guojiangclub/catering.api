<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStBannersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('st_banners', function (Blueprint $table) {
			$table->increments('id');
			$table->string('blank_url')->nullable()->comment('跳转连接');
			$table->string('blank_type')->default('self')->comment('跳转类型');
			$table->string('img')->comment('图片地址');
			$table->integer('sort')->default(99)->nullable()->comment('排序');
			$table->tinyInteger('status')->default(1)->comment('状态 1:启用 0:禁用');
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
		Schema::dropIfExists('st_banners');
	}
}

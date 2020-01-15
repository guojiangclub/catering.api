<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStGiftActivityTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('st_gift_activity', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name')->comment('活动名称');
			$table->string('title')->nullable()->comment('标题');
			$table->tinyInteger('status')->default(0);
			$table->integer('point')->default(0);
			$table->timestamp('starts_at')->nullable();
			$table->timestamp('ends_at')->nullable();
			$table->string('type', 10)->default('all')->comment('赠送方式');

			$table->timestamps();
		});

		Schema::create('st_gift_discount', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('gift_activity_id');
			$table->integer('discount_id');
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
		Schema::dropIfExists('st_gift_activity');
		Schema::dropIfExists('st_gift_discount');
	}
}

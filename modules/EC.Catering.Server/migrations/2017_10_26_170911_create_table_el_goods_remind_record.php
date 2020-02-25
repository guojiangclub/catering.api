<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableElGoodsRemindRecord extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('el_goods_remind_record', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('goods_remind_id');
			$table->string('content', 255)->comment('消息内容');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('el_goods_remind_record');
	}
}

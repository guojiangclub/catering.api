<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableElGoodsRemind extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('el_goods_remind', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('user_id')->comment('用户id');
			$table->integer('goods_id')->comment('商品id');
		    $table->string('goods_sku', 64)->comment('商品规格编号');
		    $table->integer('is_remind')->default(0)->comment('是否发送消息提醒');
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
	    Schema::drop('el_goods_remind');
    }
}

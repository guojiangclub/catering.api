<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStClerkTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('st_clerk', function (Blueprint $table) {
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
		Schema::dropIfExists('st_clerk');
	}
}

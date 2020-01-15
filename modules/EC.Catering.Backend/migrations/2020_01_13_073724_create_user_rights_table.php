<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRightsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'user_rights')) {
			Schema::create($prefix . 'user_rights', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('权益名称');
				$table->string('img')->nullable()->comment('权益图片');
				$table->tinyInteger('status')->default(1)->comment('状态');
				$table->integer('sort')->default(9)->comment('排序');
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
		Schema::dropIfExists($prefix . 'user_rights');
	}
}

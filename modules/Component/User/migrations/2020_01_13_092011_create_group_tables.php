<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'group')) {
			Schema::create($prefix . 'group', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('分组名称');
				$table->string('description')->nullable()->comment('分组说明');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'group_users')) {
			Schema::create($prefix . 'group_users', function (Blueprint $table) {
				$table->integer('user_id')->unsigned();
				$table->integer('group_id')->unsigned();
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

		Schema::dropIfExists($prefix . 'group');
		Schema::dropIfExists($prefix . 'group_users');
	}
}

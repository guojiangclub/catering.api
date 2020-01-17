<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesCategoryTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'images')) {
			Schema::create($prefix . 'images', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name');
				$table->string('url')->comment('本地url');
				$table->string('remote_url')->nullable()->comment('远程URL');
				$table->integer('category_id')->comment('分类ID');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'images_category')) {
			Schema::create($prefix . 'images_category', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('parent_id');
				$table->string('name')->comment('分类的名字');
				$table->text('description')->nullable()->comment('分类描述');
				$table->integer('sort')->default(99)->comment('分类排序');
				$table->timestamps();
				$table->softDeletes();
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

		Schema::dropIfExists($prefix . 'images');
		Schema::dropIfExists($prefix . 'images_category');
	}
}

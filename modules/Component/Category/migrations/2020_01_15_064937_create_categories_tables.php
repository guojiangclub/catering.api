<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'category')) {
			Schema::create($prefix . 'category', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('group_id')->nullable()->index()->comment('组id');
				$table->string('name')->comment('分类的名字');
				$table->text('description')->nullable()->comment('分类描述');
				$table->unsignedInteger('sort')->default(0)->comment('排序规则');
				$table->integer('status')->default(1)->comment('状态：1 有效 ，0 失效');
				$table->unsignedInteger('parent_id')->nullable();
				$table->string('path')->nullable()->default('/');
				$table->integer('level')->default(1);
				$table->unsignedInteger('_lft')->nullable();
				$table->unsignedInteger('_rgt')->nullable();
				$table->timestamps();
				$table->softDeletes();
				$table->index(['_lft', '_rgt', 'parent_id']);
			});
		}

		if (!Schema::hasTable($prefix . 'category_group')) {
			Schema::create($prefix . 'category_group', function (Blueprint $table) {
				$table->increments('id');
				$table->string('group_name')->nullable();
				$table->text('description')->nullable()->comment('分组描述');
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

		Schema::dropIfExists($prefix . 'category');
		Schema::dropIfExists($prefix . 'category_group');
	}
}

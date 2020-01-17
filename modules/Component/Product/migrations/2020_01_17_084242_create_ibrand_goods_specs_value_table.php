<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIbrandGoodsSpecsValueTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'goods_specs_value')) {
			Schema::create($prefix . 'goods_specs_value', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('spec_id');
				$table->string('name');
				$table->string('rgb')->nullable()->comment('颜色的RGB值');
				$table->string('color')->nullable()->comment('颜色规格所属色系:黑，红，蓝，绿，黄，紫，白，棕，灰'); //
				$table->integer('status')->default(1)->comment('状态：1 使用；0 不使用');
				$table->text('meta')->nullable()->comment('扩展字段');
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
		Schema::dropIfExists($prefix . 'goods_specs_value');
	}
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'goods_model')) {
			Schema::create($prefix . 'goods_model', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('模型名称');
				$table->string('spec_ids')->nullable()->comment('规格id');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'goods_attribute')) {
			Schema::create($prefix . 'goods_attribute', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('model_id')->nullable()->comment('模型ID');
				$table->tinyInteger('type')->comment('输入控件类型 1：单选   2：复选    3：下拉    4：输入框');
				$table->string('name')->comment('属性名称');
				$table->text('value')->comment('属性值');
				$table->tinyInteger('is_search')->nullable()->default(0)->comment('属性值');
				$table->tinyInteger('is_chart')->default(0);
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'goods')) {
			Schema::create($prefix . 'goods', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('商品名称');
				$table->string('goods_no')->comment('商品货号');
				$table->integer('brand_id')->comment('品牌ID');
				$table->integer('model_id')->comment('模型ID');
				$table->decimal('max_price', 15, 2)->nullable()->comment('最高销售价');
				$table->decimal('min_price', 15, 2)->nullable()->comment('最低销售价');
				$table->decimal('sell_price', 15, 2)->comment('销售价格');
				$table->decimal('market_price', 15, 2)->nullable()->comment('市场价');
				$table->decimal('min_market_price', 15, 2)->nullable()->comment('最低市场价');
				$table->decimal('cost_price', 15, 2)->nullable()->comment('成本价');
				$table->decimal('weight', 15, 2)->nullable()->comment('重量');
				$table->integer('store')->default(0)->comment('库存');
				$table->string('img')->nullable()->comment('封面图');
				$table->text('imglist')->nullable()->comment('图集');
				$table->tinyInteger('is_del')->default(0)->comment('商品状态：0正常 1删除 2下架');
				$table->tinyInteger('is_largess')->default(0)->comment('是否赠品：0否 1是');
				$table->mediumText('content')->nullable()->comment('商品描述(mobile)');
				$table->mediumText('contentpc')->nullable()->comment('商品描述(pc)');
				$table->tinyInteger('sync')->default(0)->comment('内容是否同步 0：不同步 1：同步至PC端  2：PC同步到移动端');
				$table->integer('point')->default(0)->comment('积分');
				$table->string('unit', 10)->nullable()->comment('计量单位');
				$table->integer('store_nums')->unsigned()->default(0)->comment('库存');
				$table->smallInteger('sort')->default(99)->comment('排序');
				$table->text('spec_array')->nullable()->comment('规格值');
				$table->integer('comments')->default(0)->comment('评论次数');
				$table->integer('visit')->default(0)->comment('浏览次数');
				$table->integer('favorite')->default(0)->comment('收藏次数');
				$table->integer('sale')->default(0)->comment('销量');
				$table->integer('grade')->default(0)->comment('评分总数');
				$table->tinyInteger('is_share')->default(0)->comment('是否共享 0不共享 1共享');
				$table->string('tags')->nullable()->comment('标签');
				$table->string('keywords')->nullable()->comment('关键词');
				$table->string('description')->nullable()->comment('描述');
				$table->tinyInteger('subsection')->default(0)->comment('是否线上线下同款：1 是；0 否');
				$table->integer('supplier_id')->default(1)->comment('供应商ID');
				$table->integer('is_commend');
				$table->integer('is_old');
				$table->integer('category_group');
				$table->text('collocation')->nullable()->comment('推荐搭配');
				$table->text('extra')->nullable()->comment('其他额外数据');
				$table->integer('redeem_point')->default(0);
				$table->text('extend_image')->nullable()->comment('扩展字段：图片');
				$table->text('extend_description')->nullable()->comment('扩展字段：商品简介');

				$table->index('goods_no');

				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'goods_category')) {
			Schema::create($prefix . 'goods_category', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('goods_id')->comment('商品ID');
				$table->integer('category_id')->comment('规格ID');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'goods_attribute_relation')) {
			Schema::create($prefix . 'goods_attribute_relation', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('goods_id')->comment('商品ID');
				$table->integer('attribute_id')->comment('属性ID');
				$table->string('attribute_value')->nullable()->comment('后台显示名称');
				$table->integer('model_id')->comment('模型ID');
				$table->nullableTimestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'goods_spec')) {
			Schema::create($prefix . 'goods_spec', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name');
				$table->text('value')->nullable();
				$table->integer('category_id')->nullable()->comment('所属主分类');
				$table->tinyInteger('type')->default(1)->comment('显示类型，1文字，2图片');
				$table->text('extent')->nullable();
				$table->text('extent2')->nullable();
				$table->text('spec_name')->nullable()->comment('后台显示名称');
				$table->nullableTimestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'goods_spec_relation')) {
			Schema::create($prefix . 'goods_spec_relation', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('goods_id')->comment('商品ID');
				$table->integer('spec_id')->comment('规格ID');
				$table->string('spec_value')->nullable()->comment('规格值');
				$table->string('category_id')->nullable()->comment('分类ID');
				$table->integer('sort')->default(99);
				$table->nullableTimestamps();
				$table->softDeletes();

				$table->index('goods_id');
			});
		}

		if (!Schema::hasTable($prefix . 'goods_product')) {
			Schema::create($prefix . 'goods_product', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('goods_id')->comment('商品ID');
				$table->string('products_no')->nullable()->comment('货品货号，由商品货号+横线+数字组成');
				$table->text('spec_array')->nullable()->comment('json规格数据');
				$table->integer('store_nums')->unsigned()->default(0)->comment('库存');
				$table->string('sku')->nullable()->comment('sku');
				$table->decimal('sell_price', 15, 2)->comment('销售价格');
				$table->decimal('market_price', 15, 2)->nullable()->comment('市场价');
				$table->decimal('cost_price', 15, 2)->nullable()->comment('成本价');
				$table->decimal('weight', 15, 2)->nullable()->comment('重量');
				$table->integer('is_show')->default(1);
				$table->string('bar_code')->nullable()->comment('条形编码');
				$table->timestamps();

				$table->index('sku');
				$table->index('goods_id');
			});
		}

		if (!Schema::hasTable($prefix . 'goods_photo')) {
			Schema::create($prefix . 'goods_photo', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('goods_id')->comment('商品ID');
				$table->string('url')->comment('图片URL');
				$table->integer('sort')->default(0)->comment('排序');
				$table->string('code');
				$table->integer('is_default')->default(0)->comment('是否是主图 0：否 1：是');
				$table->integer('flag')->default(1);
				$table->string('sku');
				$table->timestamps();
				$table->softDeletes();

				$table->index('sku');
				$table->index('goods_id');
			});
		}

		if (!Schema::hasTable($prefix . 'search_specs')) {
			Schema::create($prefix . 'search_specs', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('goods_id')->comment('商品ID');
				$table->integer('spec_id')->comment('规格ID');
				$table->string('spec_value')->comment('规格值');
				$table->integer('category_id')->comment('分类ID');
				$table->string('color')->nullable()->comment('颜色名称：蓝色');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'model_attribute_relation')) {
			Schema::create($prefix . 'model_attribute_relation', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('model_id')->comment('模型ID');
				$table->integer('attribute_id')->comment('属性id');
				$table->timestamps();
			});
		}

		if (!Schema::hasTable($prefix . 'supplier')) {
			Schema::create($prefix . 'supplier', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name')->comment('供应商名称');
				$table->string('code')->comment('编码 唯一');
				$table->tinyInteger('status')->default(1)->comment('状态');
				$table->string('company_name')->nullable()->comment('企业名称');
				$table->string('company_number')->nullable()->comment('企业执照注册号');
				$table->string('company_address')->nullable()->comment('企业地址');
				$table->string('company_phone')->nullable()->comment('企业电话');
				$table->string('company_limit')->nullable()->comment('营业期限');
				$table->text('company_scope')->nullable()->comment('经营范围');
				$table->string('company_license')->nullable()->comment('营业执照');
				$table->string('company_permit')->nullable()->comment('食品流通许可证');
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

		Schema::dropIfExists($prefix . 'goods_model');
		Schema::dropIfExists($prefix . 'goods_attribute');
		Schema::dropIfExists($prefix . 'goods');
		Schema::dropIfExists($prefix . 'goods_category');
		Schema::dropIfExists($prefix . 'goods_attribute_relation');
		Schema::dropIfExists($prefix . 'goods_product');
		Schema::dropIfExists($prefix . 'goods_photo');
		Schema::dropIfExists($prefix . 'search_specs');
		Schema::dropIfExists($prefix . 'model_attribute_relation');
	}
}

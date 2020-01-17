<?php

namespace GuoJiangClub\EC\Catering\Backend\Console;

use GuoJiangClub\Catering\AlbumBackend\Models\ImageCategory;
use GuoJiangClub\EC\Catering\Backend\Models\Category;
use GuoJiangClub\EC\Catering\Backend\Models\CategoryGroup;
use Illuminate\Console\Command;

class SetDefaultValueCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ibrand:store-default-value';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'install ibrand\'s store system default value.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		//1. 设置站点信息默认值
		if (empty(settings()->getSetting('store_name'))) {
			settings()->setSetting(['store_name' => 'ibrand-catering']);
		}

		if (empty(settings()->getSetting('goods_spec_color'))) {
			settings()->setSetting(['goods_spec_color' => ['黑色',
			                                               '绿色',
			                                               '白色',
			                                               '紫色',
			                                               '红色',
			                                               '黄色',
			                                               '蓝色',
			                                               '棕色',
			                                               '灰色']]);
		}

		$point_enabled = settings()->getSetting('point_enabled');
		if (empty($point_enabled)) {
			settings()->setSetting(['point_enabled' => 0]);
		}

		//前端是否显示库存
		$shop_show_store = settings()->getSetting('shop_show_store');
		if (empty($shop_show_store)) {
			settings()->setSetting(['shop_show_store' => 0]);
		}

		if (!ImageCategory::where('name', '默认分类')->first()) {
			ImageCategory::create(['parent_id' => 0, 'name' => '默认分类', 'sort' => 0]);
		}

		$category = Category::all();
		if (count($category) <= 0) {
			Category::create([
				'name'        => '默认分类',
				'description' => '默认分类',
				'sort'        => 1,
				'status'      => 1,
				'level'       => 1,
				'parent_id'   => 0,
			]);
		}

		$categoryGroup = CategoryGroup::all();
		if ($categoryGroup->count() <= 0) {
			CategoryGroup::create([
				'group_name'  => '默认分组',
				'description' => '默认分组',
			]);
		}
	}
}

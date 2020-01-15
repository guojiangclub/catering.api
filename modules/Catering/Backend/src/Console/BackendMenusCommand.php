<?php

namespace GuoJiangClub\Catering\Backend\Console;

use Illuminate\Console\Command;
use DB;

class BackendMenusCommand extends Command
{
	protected $signature = 'shitang:backend-menus';

	protected $description = 'import shitang backend menus';

	public function handle()
	{
		$lastOrder = DB::table(config('admin.database.menu_table'))->max('order');
		$parent    = DB::table(config('admin.database.menu_table'))->where('title', '客来店')->first();
		if (!$parent) {
			$id = DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => 0,
				'order'      => $lastOrder++,
				'title'      => '客来店',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'customer/shop/settings',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);

			$parent_id = $id;
		} else {
			$parent_id = $parent->id;
		}

		$settings = DB::table(config('admin.database.menu_table'))->where('title', '基础设置')->where('parent_id', $parent_id)->first();
		if (!$settings) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '基础设置',
				'icon'       => 'fa-briefcase',
				'blank'      => 1,
				'uri'        => 'customer/shop/settings',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$tmp_settings = DB::table(config('admin.database.menu_table'))->where('title', '模板消息设置')->where('parent_id', $parent_id)->first();
		if (!$tmp_settings) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '模板消息设置',
				'icon'       => 'fa-briefcase',
				'blank'      => 1,
				'uri'        => 'customer/shop/message',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$order = DB::table(config('admin.database.menu_table'))->where('title', '订单管理')->where('parent_id', $parent_id)->first();
		if (!$order) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '订单管理',
				'icon'       => 'fa-bar-chart',
				'blank'      => 1,
				'uri'        => 'customer/shop/orders',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$clerk = DB::table(config('admin.database.menu_table'))->where('title', '店员管理')->where('parent_id', $parent_id)->first();
		if (!$clerk) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '店员管理',
				'icon'       => 'fa-male',
				'blank'      => 1,
				'uri'        => 'customer/shop/clerk',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$banners = DB::table(config('admin.database.menu_table'))->where('title', '轮播图')->where('parent_id', $parent_id)->first();
		if (!$banners) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => ++$lastOrder,
				'title'      => '轮播图',
				'icon'       => 'fa-image',
				'blank'      => 1,
				'uri'        => 'customer/shop/banner',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$activity = DB::table(config('admin.database.menu_table'))->where('title', '活动管理')->where('parent_id', $parent_id)->first();
		if (!$activity) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => ++$lastOrder,
				'title'      => '活动管理',
				'icon'       => 'fa-bicycle',
				'blank'      => 1,
				'uri'        => 'customer/shop/activity',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		//营销中心
		$parent = DB::table(config('admin.database.menu_table'))->where('title', '营销中心')->first();
		if (!$parent) {
			$id = DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => 0,
				'order'      => $lastOrder++,
				'title'      => '营销中心',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'shitang/coupon',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);

			$parent_id = $id;
		} else {
			$parent_id = $parent->id;
		}

		$settings = DB::table(config('admin.database.menu_table'))->where('title', '优惠券')->where('parent_id', $parent_id)->first();
		if (!$settings) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '优惠券',
				'icon'       => 'fa-briefcase',
				'blank'      => 1,
				'uri'        => 'shitang/coupon',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$gift = DB::table(config('admin.database.menu_table'))->where('title', '新人进店礼')->where('parent_id', $parent_id)->first();
		if (!$gift) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '新人进店礼',
				'icon'       => 'fa-gift',
				'blank'      => 1,
				'uri'        => 'shitang/new_user/gift',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$birthday = DB::table(config('admin.database.menu_table'))->where('title', '生日礼')->where('parent_id', $parent_id)->first();
		if (!$birthday) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '生日礼',
				'icon'       => 'fa-gift',
				'blank'      => 1,
				'uri'        => 'shitang/birthday/gift',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$direct = DB::table(config('admin.database.menu_table'))->where('title', '定向发券')->where('parent_id', $parent_id)->first();
		if (!$direct) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '定向发券',
				'icon'       => 'fa-gift',
				'blank'      => 1,
				'uri'        => 'shitang/directional/coupon',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$shared = DB::table(config('admin.database.menu_table'))->where('title', '分享营销设置')->where('parent_id', $parent_id)->first();
		if (!$shared) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '分享营销设置',
				'icon'       => 'fa-gift',
				'blank'      => 1,
				'uri'        => 'shitang/share/setting',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$auto = DB::table(config('admin.database.menu_table'))->where('title', '智能营销')->where('parent_id', $parent_id)->first();
		if (!$auto) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '智能营销',
				'icon'       => 'fa-android',
				'blank'      => 1,
				'uri'        => 'shitang/auto/send',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$center = DB::table(config('admin.database.menu_table'))->where('title', '领劵中心')->where('parent_id', $parent_id)->first();
		if (!$center) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $parent_id,
				'order'      => $lastOrder++,
				'title'      => '领劵中心',
				'icon'       => 'fa-cc-jcb',
				'blank'      => 1,
				'uri'        => 'shitang/gift/center',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}
	}
}
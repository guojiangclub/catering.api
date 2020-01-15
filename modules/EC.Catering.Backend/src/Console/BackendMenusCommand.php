<?php

namespace GuoJiangClub\EC\Catering\Backend\Console;

use Illuminate\Console\Command;
use DB;

class BackendMenusCommand extends Command
{
	protected $signature = 'import:catering-backend-menus';

	protected $description = 'import catering backend menus';

	public function handle()
	{
		$lastOrder = DB::table(config('admin.database.menu_table'))->max('order');
		$topMenu   = DB::table(config('admin.database.menu_table'))->where('title', '会员管理')->first();
		if (!$topMenu) {
			$topMenuId = DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => 0,
				'order'      => $lastOrder++,
				'title'      => '会员管理',
				'icon'       => 'iconfont icon-huiyuanguanli-',
				'blank'      => 1,
				'uri'        => 'member/data',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		} else {
			$topMenuId = $topMenu->id;
		}

		$data = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '会员概览')->first();
		if (!$data) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '会员概览',
				'icon'       => 'iconfont icon-huiyuanguanli-',
				'blank'      => 1,
				'uri'        => 'member/data',
				'created_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$user = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '会员管理')->first();
		if (!$user) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '会员管理',
				'icon'       => 'iconfont icon-huiyuanguanli--',
				'blank'      => 1,
				'uri'        => 'member/users',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$userLevel = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '会员等级管理')->first();
		if (!$userLevel) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '会员等级管理',
				'icon'       => 'iconfont icon-huiyuandengjiguanli',
				'blank'      => 1,
				'uri'        => 'member/groups/grouplist',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$point = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '会员积分记录')->first();
		if (!$point) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '会员积分记录',
				'icon'       => 'iconfont icon-huiyuanjifenjilu',
				'blank'      => 1,
				'uri'        => 'member/points',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$balances = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '会员余额记录')->first();
		if (!$balances) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '会员余额记录',
				'icon'       => 'iconfont icon-huiyuanjifenjilu',
				'blank'      => 1,
				'uri'        => 'member/balances',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$recharge = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '储值管理')->first();
		if (!$recharge) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '储值管理',
				'icon'       => 'iconfont icon-zhifushezhi',
				'blank'      => 1,
				'uri'        => 'member/recharge',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$rights = DB::table(config('admin.database.menu_table'))->where('parent_id', $topMenuId)->where('title', '会员权益')->first();
		if (!$rights) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $topMenuId,
				'order'      => $lastOrder++,
				'title'      => '会员权益',
				'icon'       => 'iconfont icon-huiyuanqiaguanli',
				'blank'      => 1,
				'uri'        => 'member/rights',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$storeTop = DB::table(config('admin.database.menu_table'))->where('title', '商城管理')->first();
		if (!$storeTop) {
			$storeTopId = DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => 0,
				'order'      => $lastOrder++,
				'title'      => '商城管理',
				'icon'       => 'iconfont icon-shangchengguanli-',
				'blank'      => 1,
				'uri'        => 'store/point-mall/goods',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		} else {
			$storeTopId = $storeTop->id;
		}

		$point = DB::table(config('admin.database.menu_table'))->where('title', '积分商城')->first();
		if (!$point) {
			$pointTopId = DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $storeTopId,
				'order'      => $lastOrder++,
				'title'      => '积分商城',
				'icon'       => 'iconfont icon-shangpinshezhi-',
				'blank'      => 1,
				'uri'        => '',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		} else {
			$pointTopId = $point->id;
		}

		$pointGoods = DB::table(config('admin.database.menu_table'))->where('title', '积分商品管理')->first();
		if (!$pointGoods) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '积分商品管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/point-mall/goods',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$pointOrder = DB::table(config('admin.database.menu_table'))->where('title', '积分订单管理')->first();
		if (!$pointOrder) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '积分订单管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/point-mall/orders?status=all',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$models = DB::table(config('admin.database.menu_table'))->where('title', '模型管理')->first();
		if (!$models) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '模型管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/models',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$specs = DB::table(config('admin.database.menu_table'))->where('title', '规格管理')->first();
		if (!$specs) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '规格管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/specs',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$attribute = DB::table(config('admin.database.menu_table'))->where('title', '参数管理')->first();
		if (!$attribute) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '参数管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/attribute',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$brand = DB::table(config('admin.database.menu_table'))->where('title', '品牌管理')->first();
		if (!$brand) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '品牌管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/brand',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$category = DB::table(config('admin.database.menu_table'))->where('title', '分类管理')->first();
		if (!$category) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $pointTopId,
				'order'      => $lastOrder++,
				'title'      => '分类管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/category',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$imgTop = DB::table(config('admin.database.menu_table'))->where('title', '图片管理')->first();
		if (!$imgTop) {
			$imgTopId = DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $storeTopId,
				'order'      => $lastOrder++,
				'title'      => '图片管理',
				'icon'       => 'iconfont icon-tupianguanli',
				'blank'      => 1,
				'uri'        => '',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		} else {
			$imgTopId = $imgTop->id;
		}

		$image = DB::table(config('admin.database.menu_table'))->where('title', '图片列表')->first();
		if (!$image) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $imgTopId,
				'order'      => $lastOrder++,
				'title'      => '图片列表',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/image/file?category_id=1',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}

		$imageCategory = DB::table(config('admin.database.menu_table'))->where('title', '图片分类管理')->first();
		if (!$imageCategory) {
			DB::table(config('admin.database.menu_table'))->insertGetId([
				'parent_id'  => $imgTopId,
				'order'      => $lastOrder++,
				'title'      => '图片分类管理',
				'icon'       => '',
				'blank'      => 1,
				'uri'        => 'store/image/category',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time()),
			]);
		}
	}
}
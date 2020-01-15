<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use iBrand\Backend\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class AutoSendController extends Controller
{
	public function index()
	{
		$enabled_auto_marketing = settings('enabled_auto_marketing');
		$discount_coupon_rules  = settings('discount_coupon_rules');

		return Admin::content(function (Content $content) use ($enabled_auto_marketing, $discount_coupon_rules) {
			$content->description('智能营销');

			$content->breadcrumb(
				['text' => '智能营销', 'no-pjax' => 1, 'left-menu-active' => '智能营销']
			);

			$content->body(view('backend-shitang::auto.index', compact('enabled_auto_marketing', 'discount_coupon_rules')));
		});
	}
}
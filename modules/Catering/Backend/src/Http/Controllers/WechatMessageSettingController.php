<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use iBrand\Backend\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;

class WechatMessageSettingController extends Controller
{

	public function index()
	{
		$order                = settings('wechat_message_order_pay_remind');
		$deliver              = settings('wechat_message_deliver_goods_remind');
		$arrival              = settings('wechat_message_arrival_of_goods');
		$service              = settings('wechat_message_after_sales_service');
		$refund               = settings('wechat_message_goods_refund_result');
		$customer             = settings('wechat_message_customer_paid');
		$money                = settings('wechat_message_money_changed');
		$point                = settings('wechat_message_point_changed');
		$charge               = settings('wechat_message_charge_success');
		$member               = settings('wechat_message_member_grade');
		$notice               = settings('wechat_message_sales_notice');
		$result               = settings('wechat_message_refund_result');
		$grouponSuccess       = settings('wechat_message_groupon_success');
		$grouponFailed        = settings('wechat_message_groupon_failed');
		$activity_notice      = settings('wechat_message_activity_notice');
		$activity_notice_gift = settings('wechat_message_activity_notice_gift');

		$st_balance_changed       = settings('wechat_message_st_balance_changed');
		$st_point_changed         = settings('wechat_message_st_point_changed');
		$st_coupon_changed        = settings('wechat_message_st_coupon_changed');
		$st_join_success          = settings('wechat_message_st_join_success');
		$st_paid_success          = settings('wechat_message_st_paid_success');
		$st_statistics_result     = settings('wechat_message_st_statistics_result');
		$st_coupon_overdue_remind = settings('wechat_message_st_coupon_overdue_remind');

		return LaravelAdmin::content(function (Content $content) use (
			$order, $deliver, $arrival,
			$service, $refund, $customer,
			$money, $point, $charge,
			$member, $notice, $result,
			$grouponSuccess, $grouponFailed,
			$activity_notice, $activity_notice_gift,
			$st_balance_changed, $st_point_changed,
			$st_coupon_changed, $st_join_success,
			$st_paid_success, $st_statistics_result,
			$st_coupon_overdue_remind
		) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);

			$content->body(view('backend-shitang::wechat_setting.index', compact('order', 'deliver', 'arrival', 'service', 'refund', 'customer', 'money', 'point', 'charge', 'member', 'notice', 'result', 'grouponSuccess', 'grouponFailed', 'activity_notice', 'activity_notice_gift', 'st_balance_changed', 'st_point_changed', 'st_coupon_changed', 'st_join_success', 'st_paid_success', 'st_statistics_result', 'st_coupon_overdue_remind')));
		});
	}

	public function orderRemind()
	{
		$order = settings('wechat_message_order_pay_remind');

		return LaravelAdmin::content(function (Content $content) use ($order) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.order', compact('order')));
		});
	}

	public function goodsDeliver()
	{
		$deliver = settings('wechat_message_deliver_goods_remind');

		return LaravelAdmin::content(function (Content $content) use ($deliver) {

			$content->header('????????????');

			$content->breadcrumb(
				['text' => '????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.deliver', compact('deliver')));
		});
	}

	public function goodsArrival()
	{
		$arrival = settings('wechat_message_arrival_of_goods');

		return LaravelAdmin::content(function (Content $content) use ($arrival) {

			$content->header('????????????');

			$content->breadcrumb(
				['text' => '????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.arrival', compact('arrival')));
		});
	}

	public function salesService()
	{
		$service = settings('wechat_message_after_sales_service');

		return LaravelAdmin::content(function (Content $content) use ($service) {

			$content->header('??????????????????????????????');

			$content->breadcrumb(
				['text' => '??????????????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.service', compact('service')));
		});
	}

	public function goodsRefund()
	{
		$refund = settings('wechat_message_goods_refund_result');

		return LaravelAdmin::content(function (Content $content) use ($refund) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.refund', compact('refund')));
		});
	}

	public function customerPaid()
	{
		$customer = settings('wechat_message_customer_paid');

		return LaravelAdmin::content(function (Content $content) use ($customer) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.customer', compact('customer')));
		});
	}

	public function moneyChanged()
	{
		$money = settings('wechat_message_money_changed');

		return LaravelAdmin::content(function (Content $content) use ($money) {

			$content->header('????????????????????????');

			$content->breadcrumb(
				['text' => '????????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.money', compact('money')));
		});
	}

	public function pointChanged()
	{
		$point = settings('wechat_message_point_changed');

		return LaravelAdmin::content(function (Content $content) use ($point) {

			$content->header('????????????????????????');

			$content->breadcrumb(
				['text' => '????????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.point', compact('point')));
		});
	}

	public function chargeSuccess()
	{
		$charge = settings('wechat_message_charge_success');

		return LaravelAdmin::content(function (Content $content) use ($charge) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.charge', compact('charge')));
		});
	}

	public function memberGrade()
	{
		$member = settings('wechat_message_member_grade');

		return LaravelAdmin::content(function (Content $content) use ($member) {

			$content->header('????????????????????????');

			$content->breadcrumb(
				['text' => '????????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.member', compact('member')));
		});
	}

	public function salesNotice()
	{
		$notice = settings('wechat_message_sales_notice');

		return LaravelAdmin::content(function (Content $content) use ($notice) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.notice', compact('notice')));
		});
	}

	public function refundResult()
	{
		$result = settings('wechat_message_refund_result');

		return LaravelAdmin::content(function (Content $content) use ($result) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.result', compact('result')));
		});
	}

	/**
	 * ????????????????????????
	 *
	 * @return Content
	 */
	public function grouponSuccess()
	{
		$grouponSuccess = settings('wechat_message_groupon_success');

		return LaravelAdmin::content(function (Content $content) use ($grouponSuccess) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.groupon_success', compact('grouponSuccess')));
		});
	}

	public function grouponFailed()
	{
		$grouponFailed = settings('wechat_message_groupon_failed');

		return LaravelAdmin::content(function (Content $content) use ($grouponFailed) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.groupon_failed', compact('grouponFailed')));
		});
	}

	public function activityNotice()
	{
		$activity_notice = settings('wechat_message_activity_notice');

		return LaravelAdmin::content(function (Content $content) use ($activity_notice) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.activity_notice', compact('activity_notice')));
		});
	}

	public function activityNoticeGift()
	{
		$activity_notice_gift = settings('wechat_message_activity_notice_gift');

		return LaravelAdmin::content(function (Content $content) use ($activity_notice_gift) {

			$content->header('????????????????????????');

			$content->breadcrumb(
				['text' => '????????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.activity_notice_gift', compact('activity_notice_gift')));
		});
	}

	public function stPointChange()
	{
		$point = settings('wechat_message_st_point_changed');

		return LaravelAdmin::content(function (Content $content) use ($point) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_point_changed', compact('point')));
		});
	}

	public function stBalanceChange()
	{
		$money = settings('wechat_message_st_balance_changed');

		return LaravelAdmin::content(function (Content $content) use ($money) {

			$content->header('????????????????????????');

			$content->breadcrumb(
				['text' => '????????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_balance_changed', compact('money')));
		});
	}

	public function stCouponChange()
	{
		$coupon = settings('wechat_message_st_coupon_changed');

		return LaravelAdmin::content(function (Content $content) use ($coupon) {

			$content->header('???????????????');

			$content->breadcrumb(
				['text' => '???????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_coupon_changed', compact('coupon')));
		});
	}

	public function joinSuccess()
	{
		$join_success = settings('wechat_message_st_join_success');

		return LaravelAdmin::content(function (Content $content) use ($join_success) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_join_success', compact('join_success')));
		});
	}

	public function paidSuccess()
	{
		$paid_success = settings('wechat_message_st_paid_success');

		return LaravelAdmin::content(function (Content $content) use ($paid_success) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_paid_success', compact('paid_success')));
		});
	}

	public function statisticsResult()
	{
		$statistics_result = settings('wechat_message_st_statistics_result');

		return LaravelAdmin::content(function (Content $content) use ($statistics_result) {

			$content->header('??????????????????');

			$content->breadcrumb(
				['text' => '??????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_statistics_result', compact('statistics_result')));
		});
	}

	public function couponOverdueRemind()
	{
		$coupon_overdue_remind = settings('wechat_message_st_coupon_overdue_remind');

		return LaravelAdmin::content(function (Content $content) use ($coupon_overdue_remind) {

			$content->header('?????????????????????');

			$content->breadcrumb(
				['text' => '?????????????????????', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '??????????????????']

			);
			$content->body(view('backend-shitang::wechat_setting.st_coupon_overdue_remind', compact('coupon_overdue_remind')));
		});
	}

	public function save(Request $request)
	{
		$data = $request->except('_token', 'file');

		settings()->setSetting($data);

		$this->ajaxJson();
	}
}

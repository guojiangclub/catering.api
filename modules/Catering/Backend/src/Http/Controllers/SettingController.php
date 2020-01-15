<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use iBrand\Backend\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Storage;
use QrCode;

class SettingController extends Controller
{
	public function index()
	{
		$shiTangMiniProgram = settings('shitang_miniProgram_pay_config');
		if (!is_array($shiTangMiniProgram)) {
			$shiTangMiniProgram = [];
		}

		$enabled_union_pay                  = settings('enabled_union_pay');
		$point_deduction_money              = settings('point_deduction_money');
		$order_paid_give_point_unit         = settings('order_paid_give_point_unit');
		$pay_qr_code                        = settings('pay_qr_code');
		$st_pay_code_base_url               = settings('st_pay_code_base_url');
		$st_user_balance_change_template_id = settings('st_user_balance_change_template_id');
		$st_user_point_change_template_id   = settings('st_user_point_change_template_id');
		$st_user_paid_success_template_id   = settings('st_user_paid_success_template_id');
		$st_discount_expires_at             = settings('st_discount_expires_at');

		return Admin::content(function (Content $content) use ($shiTangMiniProgram, $enabled_union_pay, $point_deduction_money, $order_paid_give_point_unit, $pay_qr_code, $st_pay_code_base_url, $st_user_balance_change_template_id, $st_user_point_change_template_id, $st_user_paid_success_template_id, $st_discount_expires_at) {

			$content->header('支付设置');

			$content->body(view('backend-shitang::setting.pay', compact('shiTangMiniProgram', 'enabled_union_pay', 'point_deduction_money', 'order_paid_give_point_unit', 'pay_qr_code', 'st_pay_code_base_url', 'st_user_balance_change_template_id', 'st_user_point_change_template_id', 'st_user_paid_success_template_id', 'st_discount_expires_at')));
		});
	}

	public function base()
	{
		$complete_birthday_point            = settings('complete_birthday_point');
		$mini_program_bg_img                = settings('mini_program_bg_img');
		$shop_name                          = settings('shop_name');
		$member_shop_logo                   = settings('member_shop_logo');
		$member_rules_link                  = settings('member_rules_link');
		$personal_info_img                  = settings('personal_info_img');
		$homepage_swal_img                  = settings('homepage_swal_img');
		$enabled_union_pay                  = settings('enabled_union_pay');
		$order_paid_give_point_unit         = settings('order_paid_give_point_unit');
		$point_deduction_money              = settings('point_deduction_money');
		$st_user_balance_change_template_id = settings('st_user_balance_change_template_id');
		$st_user_point_change_template_id   = settings('st_user_point_change_template_id');
		$st_user_paid_success_template_id   = settings('st_user_paid_success_template_id');
		$manager_shop_name                  = settings('manager_shop_name');
		$manager_shop_address               = settings('manager_shop_address');
		/*$enabled_auto_marketing             = settings('enabled_auto_marketing');
		$discount_coupon_rules              = settings('discount_coupon_rules');*/

		$shiTangMiniProgram = settings('shitang_miniProgram_pay_config');
		if (!is_array($shiTangMiniProgram)) {
			$shiTangMiniProgram = [];
		}

		return Admin::content(function (Content $content) use ($complete_birthday_point, $mini_program_bg_img, $shop_name, $member_shop_logo, $member_rules_link, $personal_info_img, $homepage_swal_img, $shiTangMiniProgram, $enabled_union_pay, $order_paid_give_point_unit, $point_deduction_money, $st_user_balance_change_template_id, $st_user_point_change_template_id, $st_user_paid_success_template_id, $manager_shop_name, $manager_shop_address) {
			$content->description('基础设置');

			$content->breadcrumb(
				['text' => '基础设置', 'no-pjax' => 1, 'left-menu-active' => '基础设置']
			);

			return $content->body(view('backend-shitang::setting.base', compact('complete_birthday_point', 'mini_program_bg_img', 'shop_name', 'member_shop_logo', 'member_rules_link', 'personal_info_img', 'homepage_swal_img', 'shiTangMiniProgram', 'enabled_union_pay', 'order_paid_give_point_unit', 'point_deduction_money', 'st_user_balance_change_template_id', 'st_user_point_change_template_id', 'st_user_paid_success_template_id', 'manager_shop_name', 'manager_shop_address')));
		});
	}

	public function saveSettings(Request $request)
	{
		$data = $request->except('_token', 'file');

		if (isset($data['setting_type']) && $data['setting_type'] == 'discount_coupon_rules') {
			$rules      = [
				'discount_coupon_rules'              => 'required|array',
				'discount_coupon_rules.*.days'       => 'required|integer|min:1',
				'discount_coupon_rules.*.couponName' => 'required',
				'discount_coupon_rules.*.couponCode' => 'required',
			];
			$messages   = [
				'required'                             => ':attribute 不能为空',
				'discount_coupon_rules.required'       => '请添加营销规则',
				'discount_coupon_rules.*.days.integer' => ':attribute 必须为整数',
				'discount_coupon_rules.*.days.min'     => ':attribute 必须大于0',
			];
			$attributes = [
				'discount_coupon_rules.*.days'       => '天数',
				'discount_coupon_rules.*.couponName' => '优惠券',
				'discount_coupon_rules.*.couponCode' => '优惠券',
			];
			$validator  = Validator::make($data, $rules, $messages, $attributes);
			if ($validator->fails()) {
				return $this->ajaxJson(false, [], 500, $validator->messages()->first());
			}
		}

		settings()->setSetting($data);

		return $this->ajaxJson();
	}

	public function payQrCode()
	{
		$st_pay_code_base_url = settings('st_pay_code_base_url');

		$logo     = '/public/assets/backend/shitang/img/basicprofile.jpeg';
		$savePath = 'shitang_pay/shitang_pay.png';
		$res      = QrCode::format('png')->size(200)->margin(1)->merge($logo, .4)->errorCorrection('H')->generate($st_pay_code_base_url);
		Storage::disk('admin')->put($savePath, $res);

		return response()->json([
			'status'  => true,
			'message' => '操作成功',
			'data'    => [
				'url' => Storage::disk('admin')->url($savePath),
			],
		]);
	}

	public function shareSetting()
	{
		return Admin::content(function (Content $content) {
			$content->description('分享营销设置');

			$content->breadcrumb(
				['text' => '分享设置', 'no-pjax' => 1, 'left-menu-active' => '分享营销设置']
			);

			return $content->body(view('backend-shitang::share_setting.index'));
		});
	}
}
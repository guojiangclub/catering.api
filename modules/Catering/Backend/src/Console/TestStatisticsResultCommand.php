<?php

namespace GuoJiangClub\Catering\Backend\Console;

use Illuminate\Console\Command;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use iBrand\Wechat\Backend\Facades\NoticeService;
use GuoJiangClub\Catering\Core\Auth\User;
use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Recharge\Models\BalanceOrder;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;

class TestStatisticsResultCommand extends Command
{
	protected $signature = 'test:statistics-result';

	protected $description = 'test statistics result template message';

	public function handle()
	{
		$template_settings = app('system_setting')->getSetting('wechat_message_st_statistics_result');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return;
		}

		$clerks = Clerk::where('status', 1)->where('receive_template_message', 1)->whereNotNull('openid')->get();
		if ($clerks->count() <= 0) {
			return;
		}

		$startDay         = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';
		$endDay           = date('Y-m-d', strtotime('-1 day')) . ' 23:59:59';
		$newUser          = User::whereBetween('created_at', [$startDay, $endDay])->count();
		$userBind         = UserBind::where('type', 'wechat')->whereBetween('created_at', [$startDay, $endDay])->count();
		$orderTotal       = 0;
		$adjustmentsTotal = 0;
		$rechargeTotal    = 0;
		$balanceTotal     = 0;
		$couponTotal      = 0;

		$orders = Order::whereIn('status', [2, 3, 4, 5])->whereBetween('created_at', [$startDay, $endDay])->get(['total', 'adjustments_total']);
		if ($orders->count() > 0) {
			$orderTotal       = $orders->sum('total') / 100;
			$adjustmentsTotal = $orders->sum('adjustments_total') / 100;
		}

		$balanceOrders = BalanceOrder::where('pay_status', 1)->whereNotNull('pay_time')->whereBetween('created_at', [$startDay, $endDay])->get(['amount', 'pay_amount']);
		if ($balanceOrders->count() > 0) {
			$rechargeTotal = $balanceOrders->sum('amount') / 100;
		}

		$payments = Payment::where('status', Payment::STATUS_COMPLETED)->where('channel', 'balance')->whereBetween('paid_at', [$startDay, $endDay])->get(['amount']);
		if ($payments->count() > 0) {
			$balanceTotal = $payments->sum('amount') / 100;
		}

		$coupons = Coupon::whereNotNull('used_at')->whereBetween('used_at', [$startDay, $endDay])->get();
		if ($coupons->count() > 0) {
			$couponTotal = $coupons->count();
		}

		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => app('system_setting')->getSetting('shop_name'),
			'keyword2' => date('Y年m月d日 H:i', time()),
			'keyword3' => '昨日新增会员' . $newUser . '人，公众号关注' . $userBind . '人，会员买单金额' . $orderTotal . '元，优惠金额' . abs($adjustmentsTotal) . '元，储值金额' . $rechargeTotal . '元，消耗金额' . $balanceTotal . '元，核销优惠券' . $couponTotal . '张',
			'remark'   => '',
		];

		$touser = $clerks->pluck('openid')->all();
		$data   = [
			'template_id' => $template_settings['template_id'],
			'url'         => '',
			'touser'      => $touser,
			'data'        => $template,
		];

		NoticeService::sendMessage($data, app('system_setting')->getSetting('wechat_app_id'));
	}
}
<?php

namespace GuoJiangClub\Catering\Server\Listeners;

use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserBindRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use GuoJiangClub\Catering\Component\Recharge\Models\BalanceOrder;
use GuoJiangClub\Catering\Backend\Models\Order;
use EasyWeChat;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Core\Notifications\PaidSuccess;
use Storage;
use QrCode;

class UserPropertyChangeEvent
{
	protected $balanceRepository;
	protected $pointRepository;
	protected $miniProgram;
	protected $userRepository;
	protected $userBindRepository;

	public function __construct(PointRepository $pointRepository, BalanceRepository $balanceRepository, UserBindRepository $userBindRepository, UserRepository $userRepository)
	{
		$this->balanceRepository  = $balanceRepository;
		$this->pointRepository    = $pointRepository;
		$this->userRepository     = $userRepository;
		$this->userBindRepository = $userBindRepository;
		$this->miniProgram        = EasyWeChat::miniProgram('shitang');
	}

	public function balanceChange($order, $form_id)
	{
		$template_id = settings('st_user_balance_change_template_id');
		if (!$template_id || !$form_id) {
			return false;
		}

		$user = $this->userRepository->findWhere(['id' => $order->user_id, 'status' => 1])->first();
		if (!$user) {
			return false;
		}

		$userBind = $this->userBindRepository->findWhere(['user_id' => $user->id, 'type' => 'miniprogram', 'app_id' => env('SHITANG_MINI_PROGRAM_APPID')])->first();
		if (!$userBind) {
			return false;
		}

		$balance    = $this->balanceRepository->getSum($order->user_id);
		$pointValid = $this->pointRepository->getSumPointValid($order->user_id, 'default');
		if ($order instanceof BalanceOrder) {
			$award = number_format($order->pay_amount / 100, 2, ".", "");
			$data  = [
				'keyword1' => date('Y-m-d H:i', strtotime($order->created_at)),
				'keyword2' => '余额储值',
				'keyword3' => number_format($order->amount / 100, 2, '.', ''),
				'keyword4' => number_format($balance / 100, 2, '.', ''),
				'keyword5' => '余额储值获得积分：' . $award . '，当前可用积分：' . $pointValid,
			];
		}

		if ($order instanceof Order && $order->type == Order::TYPE_BALANCE) {
			$data = [
				'keyword1' => date('Y-m-d H:i', strtotime($order->created_at)),
				'keyword2' => '消费支出',
				'keyword3' => $order->used_balance_amount,
				'keyword4' => number_format($balance / 100, 2, '.', ''),
				'keyword5' => '请前往个人中心查看余额明细',
			];
		}

		if ($order instanceof Order && $order->type == Order::TYPE_BALANCE_AND_POINT) {
			$data = [
				'keyword1' => date('Y-m-d H:i', strtotime($order->created_at)),
				'keyword2' => '消费支出',
				'keyword3' => $order->used_balance_amount,
				'keyword4' => number_format($balance / 100, 2, '.', ''),
				'keyword5' => '订单使用积分抵扣：' . $order->redeem_point . '，当前可用积分：' . $pointValid,
			];
		}

		$this->miniProgram->template_message->send([
			'touser'      => $userBind->open_id,
			'template_id' => $template_id,
			'page'        => 'pages/index/index/index',
			'form_id'     => $form_id,
			'data'        => $data,
		]);

		return true;
	}

	public function pointChange($order, $form_id)
	{
		$template_id = settings('st_user_point_change_template_id');
		if (!$template_id || !$form_id) {
			return false;
		}

		$user = $this->userRepository->findWhere(['id' => $order->user_id, 'status' => 1])->first();
		if (!$user) {
			return false;
		}

		$userBind = $this->userBindRepository->findWhere(['user_id' => $user->id, 'type' => 'miniprogram', 'app_id' => env('SHITANG_MINI_PROGRAM_APPID')])->first();
		if (!$userBind) {
			return false;
		}

		$pointValid = $this->pointRepository->getSumPointValid($order->user_id, 'default');
		$data       = [
			'keyword1' => $order->order_user_name,
			'keyword2' => '本次使用' . $order->redeem_point . '积分',
			'keyword3' => '',
			'keyword4' => '',
			'keyword5' => '当前可用积分：' . $pointValid,
		];

		$this->miniProgram->template_message->send([
			'touser'      => $userBind->open_id,
			'template_id' => $template_id,
			'page'        => 'pages/index/index/index',
			'form_id'     => $form_id,
			'data'        => $data,
		]);

		return true;
	}

	public function paidSuccess($order)
	{
		$user = $this->userRepository->findWhere(['id' => $order->user_id, 'status' => 1])->first();
		if (!$user) {
			return false;
		}

		$remark          = '';
		$balance_payment = $order->payments()->where('channel', Payment::TYPE_BALANCE)->where('status', Payment::STATUS_COMPLETED)->first();
		if ($balance_payment) {
			$balance = $this->balanceRepository->getSum($order->user_id);
			$remark  = '订单使用余额：' . number_format($balance_payment->amount / 100, 2, '.', '') . '元' . '，当前余额：' . number_format($balance / 100, 2, '.', '');
		}

		$point_adjustment = $order->adjustments()->where('origin_type', 'point')->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->first();
		if ($point_adjustment) {
			if ($remark) {
				$remark .= '，订单使用积分：' . $order->redeem_point . '，抵扣' . number_format(abs($point_adjustment->amount) / 100, 2, '.', '') . '元';
			} else {
				$remark = '订单使用积分：' . $order->redeem_point . '，抵扣' . number_format(abs($point_adjustment->amount) / 100, 2, '.', '') . '元';
			}
		}

		$coupon_adjustment = $order->adjustments()->where('origin_type', 'coupon')->where('type', Adjustment::ORDER_DISCOUNT_ADJUSTMENT)->first();
		if ($coupon_adjustment) {
			if ($remark) {
				$remark .= '，订单使用优惠券：优惠金额' . number_format(abs($coupon_adjustment->amount) / 100, 2, '.', '') . '元';
			} else {
				$remark = '订单使用优惠券：优惠金额' . number_format(abs($coupon_adjustment->amount) / 100, 2, '.', '') . '元';
			}
		}

		$pointValid = $this->pointRepository->getSumPointValid($order->user_id, 'default');
		if ($remark) {
			$remark .= '，支付成功获得积分：' . $order->paid_amount . '，当前可用积分：' . $pointValid;
		} else {
			$remark = '支付成功获得积分：' . $order->paid_amount . '，当前可用积分：' . $pointValid;
		}

		$user->notify(new PaidSuccess(['order' => $order, 'remark' => $remark]));

		return true;
	}

	public function generateQrCode($user)
	{
		if (!$user->card_no) {
			$user->card_no = create_member_card_no();
			$user->save();
		}

		if ($user->qr_code_url) {
			return;
		}

		$qrCodeSavePath = 'user/qrCode/' . $user->id . '_' . $user->card_no . '.png';
		if (!Storage::disk('public')->exists($qrCodeSavePath)) {
			$logo = settings('member_shop_logo');
			if ($logo) {
				$res = QrCode::format('png')->size(200)->margin(0.5)->merge($logo, 0.3, true)->errorCorrection('H')->generate($user->card_no);
			} else {
				$res = QrCode::format('png')->size(200)->margin(0.5)->errorCorrection('H')->generate($user->card_no);
			}

			Storage::disk('public')->put($qrCodeSavePath, $res);
		}

		$user->qr_code_url = Storage::disk('public')->url($qrCodeSavePath);
		$user->save();
	}

	public function subscribe($events)
	{
		$events->listen(
			'st.on.balance.changed',
			'GuoJiangClub\Catering\Server\Listeners\UserPropertyChangeEvent@balanceChange'
		);

		$events->listen(
			'st.on.point.changed',
			'GuoJiangClub\Catering\Server\Listeners\UserPropertyChangeEvent@pointChange'
		);

		$events->listen(
			'st.on.paid.success',
			'GuoJiangClub\Catering\Server\Listeners\UserPropertyChangeEvent@paidSuccess'
		);

		$events->listen(
			'st.user.generate.qrcode',
			'GuoJiangClub\Catering\Server\Listeners\UserPropertyChangeEvent@generateQrCode'
		);
	}
}
<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use ElementVip\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Server\Service\NotifyService;
use ElementVip\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Point;
use Illuminate\Http\Request;
use Validator;

class UnionPayController extends Controller
{
	protected $orderRepository;
	protected $pointRepository;
	protected $notifyService;
	protected $chargeContract;
	protected $balanceRepository;

	public function __construct(OrderRepository $orderRepository,
	                            PointRepository $pointRepository,
	                            NotifyService $notifyService,
	                            PaymentChargeContract $chargeContract,
	                            BalanceRepository $balanceRepository)
	{
		$this->orderRepository   = $orderRepository;
		$this->pointRepository   = $pointRepository;
		$this->notifyService     = $notifyService;
		$this->chargeContract    = $chargeContract;
		$this->balanceRepository = $balanceRepository;
	}

	public function unifiedOrder(Request $request)
	{
		$input = $request->except('_token');
		$rules = [
			'total_amount' => 'required|integer|min:1',
			'amount'       => 'required|integer|min:0',
			'balance'      => 'required|integer',
			'point'        => 'required|numeric|min:0',
			'point_money'  => 'required|integer|min:0',
			'coupon_id'    => 'required|integer',
			'channel'      => 'required|in:wx_lite,balance,balance_point,point',
			'code'         => 'required_if:channel,wx_lite',
		];

		$message = [
			'required'    => ':attribute 不能为空',
			'required_if' => ':attribute 不能为空',
			'integer'     => ':attribute 必须为整数',
			'numeric'     => ':attribute 必须为数字',
			'min'         => ':attribute 错误',
			'filled'      => ':attribute 不能为空',
			'channel.in'  => '支付方式错误',
		];

		$attributes = [
			'total_amount' => '订单金额',
			'amount'       => '支付金额',
			'balance'      => '余额',
			'point'        => '积分',
			'point_money'  => '积分抵扣金额',
			'coupon_id'    => '优惠券',
			'code'         => 'code凭证',
			'channel'      => '支付方式',
		];

		$validator = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->failed($show_warning);
		}

		if ($input['amount'] > $input['total_amount']) {
			return $this->failed('支付金额错误');
		}

		$channel = $this->checkPayChannel($input);
		if (!$channel || $channel != $input['channel']) {
			return $this->failed('支付方式错误');
		}

		$user    = request()->user();
		$service = app($channel);

		$result = $service->unifiedOrder($input, $user);
		if ($result['status']) {
			return $this->success($result['data']);
		} else {
			return $this->failed($result['message']);
		}
	}

	public function checkPayChannel($params)
	{
		if (intval($params['amount']) > 0) {
			return Payment::TYPE_WX_LITE;
		}

		if (intval($params['balance']) > 0 && intval($params['balance']) === intval($params['total_amount'])) {
			return Payment::TYPE_BALANCE;
		}

		$money = (int) round($params['point'] * settings('point_deduction_money'), 0, PHP_ROUND_HALF_DOWN);
		if ($params['point'] > 0 && $params['point_money'] > 0 && intval($params['point_money']) === intval($params['total_amount']) && $money == $params['total_amount']) {
			return Payment::TYPE_POINT;
		}

		if ($params['total_amount'] > 0 && $params['balance'] > 0 && $params['point'] > 0 && $params['point_money'] > 0 && (intval($params['balance']) + intval($params['point_money']) === intval($params['total_amount']))) {
			return Payment::TYPE_MIXED;
		}

		return false;
	}

	public function paidSuccess($order_no)
	{
		$user = request()->user();
		if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
			return $this->failed('订单不存在');
		}

		if ($user->cant('update', $order)) {
			return $this->failed('没有权限操作');
		}

		if ($order->type == Order::TYPE_BALANCE || $order->type == Order::TYPE_ALL_POINT || $order->type == Order::TYPE_BALANCE_AND_POINT) {
			switch ($order->type) {
				case Order::TYPE_BALANCE:
					$channel = Payment::TYPE_BALANCE;
					break;
				case Order::TYPE_BALANCE_AND_POINT:
					$channel = Payment::TYPE_MIXED;
					break;
				case Order::TYPE_ALL_POINT:
					$channel = Payment::TYPE_POINT;
					break;
			}

			$service = app($channel);
			$service->checkout($order);
			$order = $this->orderRepository->getOrderByNo($order_no);
		}

		if ($order->status == Order::STATUS_TEMP && $order->type == Order::TYPE_DEFAULT) {
			$result = $this->chargeContract->queryByOutTradeNumber($order_no);
			if (!empty($result) AND $result['attachedData']['type'] == 'order') {
				$this->notifyService->notify($order_no, $result, $result['attachedData']);
				$order = $this->orderRepository->getOrderByNo($order_no);
			}
		}

		if ($order->status == Order::STATUS_PAY) {
			$pointInfo = $this->getPointInfo($order);
			if ($order->type == Order::TYPE_DEFAULT) {
				event('st.on.paid.success', [$order]);
			}

			if ($order->total > 0) {
				event('order.paid', [$order]);
			}

			return $this->success(['order' => $order, 'pointInfo' => $pointInfo, 'payment' => $this->getPayment($order)]);
		}

		return $this->failed('订单未支付或支付失败');
	}

	protected function getPointInfo($order)
	{
		$pointUsed = Point::where(['item_type' => Order::class, 'item_id' => $order->id, 'action' => Point::ACTION_ORDER_USED])->first();
		$pointUsed = $pointUsed ? $pointUsed->value : 0;

		$pointAdded = 0;
		if ($order->type == Order::TYPE_DEFAULT) {
			$pointAdded = Point::where(['item_type' => Order::class, 'item_id' => $order->id, 'action' => Point::ACTION_ORDER_PAID])->first();
			$pointAdded = $pointAdded ? $pointAdded->value : 0;
		}

		$pointTotal = $this->pointRepository->getSumPointValid($order->user_id, 'default');

		return [
			'pointUsed'  => $pointUsed,
			'pointAdded' => $pointAdded,
			'pointTotal' => $pointTotal,
		];
	}

	protected function getPayment($order)
	{
		$payment = $order->payments->last();

		if (!$payment) {
			return '积分支付';
		}

		return $payment;
	}

	public function cancel($order_no)
	{
		$order = $this->orderRepository->getOrderByNo($order_no);
		if (!$order) {
			return $this->failed('订单不存在');
		}

		$payment = $order->payments()->where('channel', Payment::TYPE_BALANCE)->first();
		if ($payment) {
			$balance = $this->balanceRepository->findWhere(['user_id' => $order->user_id, 'type' => 'order_payment', 'origin_id' => $payment->id, 'origin_type' => Payment::class])->first();
			if ($balance) {
				$balance->delete();
			}

			$payment->delete();
		}

		$adjustment = $order->adjustments()->where('origin_type', 'point')->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->first();
		if ($adjustment) {
			$point = $this->pointRepository->findWhere(['user_id' => $order->user_id, 'action' => 'order_point', 'item_type' => Order::class, 'item_id' => $order->id])->first();
			if ($point) {
				$point->delete();
			}

			$adjustment->delete();
		}

		$adjustment = $order->adjustments()->where('origin_type', 'coupon')->where('type', Adjustment::ORDER_DISCOUNT_ADJUSTMENT)->first();
		if ($adjustment) {
			$coupon = Coupon::find($adjustment->origin_id);
			if ($coupon) {
				$coupon->used_at = null;
				$coupon->save();
			}

			$adjustment->delete();
		}

		return $this->success();
	}
}
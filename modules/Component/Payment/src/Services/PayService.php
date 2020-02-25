<?php

namespace GuoJiangClub\Catering\Component\Payment\Services;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Order\Processor\OrderProcessor;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use GuoJiangClub\EC\Catering\Notifications\ChargeSuccess;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;

class PayService
{
	private $orderRepository;
	private $orderProcessor;
	private $pointRepository;

	public function __construct(
		OrderRepository $orderRepository
		, OrderProcessor $orderProcessor
		, PointRepository $pointRepository
	)
	{
		$this->orderRepository = $orderRepository;
		$this->orderProcessor  = $orderProcessor;
		$this->pointRepository = $pointRepository;
	}

	public function paySuccess($charge)
	{
		$type = $charge['type'];
		//充值
		if ($type == 'recharge') {
			$this->RechargePaySuccess($charge);
			//订单
		} else {
			if ($type == 'order') {
				$this->OrderPaySuccess($charge);
				//活动
			} else {
				if ($type == 'activity') {
					$this->ActivityPaySuccess($charge);
				}
			}
		}
	}

	protected function RechargePaySuccess($charge)
	{

		$order_no = $charge['out_trade_no'];

		$type = $charge['type'];

		$order = BalanceOrder::where('order_no', $order_no)->first();

		if ($order AND $order->pay_status == 0 AND $order->pay_amount == $charge['total_amount'] * 100) {

			$order->pay_status = 1;
			$order->pay_time   = Carbon::now();
			$order->save();

			$balance = Balance::create(['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]);

			event('recharge.success', [$order]);
			$user = User::find($order->user_id);
			$user->notify(new ChargeSuccess(['charge' => ['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]]));
		}
	}

	protected function OrderPaySuccess($charge)
	{
		\Log::info($charge);
		$order_no = $charge['out_trade_no'];
		$type     = $charge['type'];
		//更改订单状态
		$order = $this->orderRepository->getOrderByNo($order_no);

		$need_pay  = $order->getNeedPayAmount();
		$pay_state = $charge['total_amount'] * 100 - $need_pay;

		$order_pay = Payment::where('channel_no', $charge['trade_no'])->where('order_id', $order->id)->first();
		if ($order_pay And $order_pay->channel != 'balance') {
			return;
		}

		if ($pay_state >= 0) {
			$order = $this->orderRepository->getOrderByNo($order_no);

			$payment = new Payment([
				'order_id'     => $order->id,
				'channel'      => $charge['channel'],
				'amount'       => $charge['total_amount'] * 100,
				'status'       => Payment::STATUS_COMPLETED
				, 'channel_no' => $charge['trade_no'],
				'pingxx_no'    => ''
				, 'paid_at'    => $charge['send_pay_date'],
			]);

			$order->payments()->save($payment);

			event('order.customer.paid', [$order]);

			$this->orderProcessor->process($order);
		}
	}
}
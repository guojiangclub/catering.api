<?php

namespace GuoJiangClub\Catering\Server\Service;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\Catering\Component\Recharge\Models\BalanceOrder;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Backend\Models\Point;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Backend\Models\Order;

class NotifyService
{
	protected $orderRepository;
	protected $chargeContract;

	public function __construct(OrderRepository $orderRepository,
	                            PaymentChargeContract $chargeContract)
	{
		$this->orderRepository = $orderRepository;
		$this->chargeContract  = $chargeContract;
	}

	public function notify($order_no, $notify, $attach)
	{
		return call_user_func([$this, $attach['type']], $order_no, $notify, $attach);
	}

	public function order($order_no, $notify, $attach)
	{
		$order = $this->orderRepository->getOrderByNo($order_no);
		if (!$order) {
			\Log::info('订单号：' . $order_no . ' 不存在');

			return false;
		}

		$need_pay = $order->getNeedPayAmount();

		$pay_state = $notify['totalAmount'] - $need_pay;

		$order_pay = Payment::where('channel_no', $notify['merOrderId'])->where('order_id', $order->id)->first();
		if ($order_pay && $order_pay->status == 'completed' && $order_pay->channel != 'balance') {
			return true;
		}

		if ($pay_state >= 0) {
			$payment = new Payment([
				'order_id'   => $order->id,
				'channel'    => $attach['channel'],
				'amount'     => $notify['totalAmount'],
				'status'     => Payment::STATUS_COMPLETED,
				'channel_no' => $notify['merOrderId'],
				'paid_at'    => $notify['payTime'],
				'details'    => json_encode($notify),
			]);

			$order->payments()->save($payment);
			$order->status     = Order::STATUS_PAY;
			$order->pay_time   = Carbon::now();
			$order->pay_status = 1;
			$order->save();

			$this->chargeContract->createPaymentLog('result_pay', Carbon::now(), $notify['merOrderId'], '', '', $notify['totalAmount'], $attach['channel'], $attach['type'], 'SUCCESS', $order->user_id, $notify);

			$award = number_format($notify['totalAmount'] / 100, 2, ".", "");
			Point::create(['user_id' => $order->user_id, 'action' => Point::ACTION_ORDER_PAID, 'note' => '消费送积分：' . $award . ',订单号：' . $order_no, 'value' => $award, 'valid_time' => 0, 'item_type' => Order::class, 'item_id' => $order->id,]);

			event('point.change', $order->user_id);

			return true;
		}

		return false;
	}

	public function recharge($order_no, $notify, $attach)
	{
		$order = BalanceOrder::where('order_no', $order_no)->first();

		if ($order AND $order->pay_status == 0 AND $order->pay_amount == $notify['totalAmount']) {
			$order->pay_status = 1;
			$order->pay_time   = Carbon::now();
			$order->save();

			$sum = Balance::sumByUser($order->user_id);
			if (!is_numeric($sum)) {
				$sum = 0;
			} else {
				$sum = (int) $sum;
			}

			Balance::create(['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'current_balance' => $sum + $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]);

			$this->chargeContract->createPaymentLog('result_pay', Carbon::now(), $notify['merOrderId'], '', '', $notify['totalAmount'], $attach['channel'], $attach['type'], 'SUCCESS', $order->user_id, $notify);

			$award = number_format($order->pay_amount / 100, 2, ".", "");
			Point::create(['user_id' => $order->user_id, 'action' => Point::ACTION_BALANCE_RECHARGE, 'note' => '充值送积分：' . $award . ',订单号：' . $order_no, 'value' => $award, 'valid_time' => 0, 'item_type' => BalanceOrder::class, 'item_id' => $order->id,]);

			event('point.change', $order->user_id);

			return true;
		}

		return false;
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Payment\Services;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Order\Processor\OrderProcessor;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use ElementVip\Notifications\ChargeSuccess;

class PaymentService
{
	private $orderRepository;
	private $orderProcessor;

	public function __construct(OrderRepository $orderRepository
		, OrderProcessor $orderProcessor)
	{
		$this->orderRepository = $orderRepository;
		$this->orderProcessor  = $orderProcessor;
	}

	/**
	 * 支付成功操作，只有pingpp webhooks通知时才能更改订单状态
	 *
	 * @param array $charge
	 */
	public function paySuccess(array $charge)
	{
		$order_no = $charge['metadata']['order_sn'];
		$type     = $charge['metadata']['type'];

		call_user_func([$this, $type], $order_no, $charge);
	}

	public function recharge($order_no, $charge)
	{
		$order = BalanceOrder::where('order_no', $order_no)->first();

		if ($order AND $order->pay_status == 0 AND $order->pay_amount == $charge['amount']) {
			$order->pay_status = 1;
			$order->pay_time   = Carbon::now();
			$order->save();

			$sum = Balance::sumByUser($order->user_id);
			if (!is_numeric($sum)) {
				$sum = 0;
			} else {
				$sum = (int) $sum;
			}

			$balance = Balance::create(['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'current_balance' => $sum + $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]);

			event('recharge.success', [$order]);
			$user = User::find($order->user_id);
			$user->notify(new ChargeSuccess(['charge' => ['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]]));
		}
	}

	public function order($order_no, $charge)
	{
		//更改订单状态
		$order = $this->orderRepository->getOrderByNo($order_no);

		$need_pay = $order->getNeedPayAmount();

		$pay_state = $charge['amount'] - $need_pay;

		$order_pay = Payment::where('channel_no', $charge['transaction_no'])->where('order_id', $order->id)->first();
		if ($order_pay && $order_pay->status == 'completed' && $order_pay->channel != 'balance') {
			return;
		}

		if ($pay_state >= 0) {
			$order = $this->orderRepository->getOrderByNo($order_no);

			$payment = new Payment(['order_id'     => $order->id, 'channel' => $charge['channel'],
			                        'amount'       => $charge['amount'], 'status' => Payment::STATUS_COMPLETED
			                        , 'channel_no' => $charge['transaction_no'], 'pingxx_no' => $charge['id']
			                        , 'paid_at'    => Carbon::createFromTimestamp($charge['time_paid'])
			                        , 'details'    => isset($charge['details']) ? $charge['details'] : '']);

			/*$order->addPayment($payment);*/
			$order->payments()->save($payment);

			event('order.customer.paid', [$order]);

			event('order.seckill.sell.num', [$order]);

			/*event('order.groupon.sell.num', [$order]);*/

			$this->orderProcessor->process($order);
		}

		/* //写入订单日志
		 OrderlogService::log($charge['metadata']['user_id']
			 , $order_no
			 , '订单支付'
			 , '订单【' . $order_no . '】支付成功'
		 );*/

		//写入支付日志
		/*$this->log($charge['metadata']['user_id'], $order_no, self::PAY_ORDER_SUCCESS, $charge, '支付成功', $charge['client_ip']);*/
	}
}

<?php

namespace GuoJiangClub\Catering\Component\Order\Processor;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use Illuminate\Contracts\Events\Dispatcher;

class OrderProcessor
{
	protected $event;

	public function __construct(Dispatcher $event)
	{
		$this->event = $event;
	}

	public function create($order)
	{
		if ($order->count > 0) {
			$order->save();
			$this->event->fire('order.created');

			return $order;
		}

		return false;
	}

	public function process(Order $order)
	{
		if ($order->status == Order::STATUS_TEMP) {
			$this->submitOrder($order);
		}

		if ($order->status == Order::STATUS_NEW OR $order->status == Order::STATUS_PAY_PARTLY) {
			$this->payOrder($order);
		}

		if ($order->status == Order::STATUS_DELIVERED) {
			$this->received($order);
		}

		if ($order->status == Order::STATUS_RECEIVED) {
			$this->review($order);
		}
	}

	/**
	 * 正式生成订单
	 *
	 * @param $order
	 */
	private function submitOrder(Order $order)
	{
		$order->status      = Order::STATUS_NEW;
		$order->submit_time = Carbon::now();
		$order->save();

		if ($order->total === 0) {
			$this->payOrder($order);
		}

		if (request('agent_code')) {
			$this->event->fire('agent.user.relation', [request('agent_code'), $order->user_id]);
		}

		if (request('shop_id')) {
			$this->event->fire('shop.user.submitOrder', [$order->user_id]);
		}

		if ($order->channel == 'ec') { //如果是电商订单
			$this->event->fire('order.submitted', [$order]);
			$this->event->fire('purchase.record.on.order.submitted', [$order->id, $order->user_id]);
		}
	}

	/**
	 * 支付订单
	 *
	 * @param $order
	 */
	private function payOrder(Order $order)
	{
		if ($order->total == 0) {
			$order->pay_type   = 'free';
			$order->pay_time   = Carbon::now();
			$order->status     = Order::STATUS_PAY;
			$order->pay_status = 1;
			$order->save();
			$this->event->fire('order.paid', [$order]);
		} else {
			if ($order->total <= $order->getPaidAmount()) {
				$order->status     = Order::STATUS_PAY;
				$order->pay_time   = Carbon::now();
				$order->pay_status = 1;
				$order->save();
				$this->event->fire('order.paid', [$order]);
			} else {
				$order->save();
			}
		}
	}

	public function cancel(Order $order)
	{
		if ($order->status == Order::STATUS_NEW) {
			$order->status          = Order::STATUS_CANCEL;
			$order->completion_time = Carbon::now();
			$order->cancel_reason   = '用户取消';
			$order->save();
			event('order.canceled', $order->id);
			event('agent.order.canceled', $order->id);
			$this->event->fire('purchase.record.on.order.cancel', [$order->id, $order->user_id]);

			return true;
		}

		return false;
	}

	public function received($order)
	{
		$order->status      = Order::STATUS_RECEIVED;
		$order->accept_time = Carbon::now();
		$order->save();
		$this->event->fire('order.received', [$order]);
	}

	public function delete($order)
	{
		if ($order->status == Order::STATUS_CANCEL) {
			$order->status = Order::STATUS_DELETED;
			$order->save();

			return true;
		}

		return false;
	}

	private function review(Order $order)
	{
		if ($order->countItems() == $order->countComments()) {
			$order->status          = Order::STATUS_COMPLETE;
			$order->completion_time = Carbon::now();
			$order->save();
			$this->event->fire('order.reviewed', [$order]);
		}
	}

}
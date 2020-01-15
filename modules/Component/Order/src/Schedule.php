<?php

namespace GuoJiangClub\Catering\Component\Order;

use GuoJiangClub\Catering\Component\Scheduling\Schedule\Scheduling;
use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Order;

class Schedule extends Scheduling
{
	public function schedule()
	{
		//订单自动收货
		$this->schedule->call(function () {

			$delay  = app('system_setting')->getSetting('order_auto_receive_time') ? app('system_setting')->getSetting('order_auto_receive_time') : 14;
			$orders = Order::where('status', 3)->whereRaw('(DATEDIFF(now(),updated_at) >= ' . $delay . ')')->get();
			foreach ($orders as $order) {
				$order->status      = Order::STATUS_RECEIVED;
				$order->accept_time = Carbon::now();
				$order->save();
				event('order.received', [$order]);
			}
		})->daily();

		$this->schedule->call(function () {

			$delayTime = app('system_setting')->getSetting('order_auto_cancel_time') ? app('system_setting')->getSetting('order_auto_cancel_time') : 1440;
			$delayTime = Carbon::now()->addMinute(-$delayTime);
			$orders    = Order::where('status', Order::STATUS_NEW)->where('submit_time', '<', $delayTime->toDateTimeString())->get();

			foreach ($orders as $order) {

				$order->status          = Order::STATUS_CANCEL;
				$order->completion_time = Carbon::now();
				$order->cancel_reason   = '过期未付款';
				$order->save();

				event('order.canceled', $order->id);
				event('agent.order.canceled', $order->id);
			}
		})->everyFiveMinutes();

		/*订单自动完成*/
		$this->schedule->call(function () {
			$delay  = app('system_setting')->getSetting('order_auto_complete_time') ? app('system_setting')->getSetting('order_auto_complete_time') : 7;
			$orders = Order::where('status', 4)->whereRaw('(DATEDIFF(now(),accept_time) >= ' . $delay . ')')->get();

			if (count($orders)) {
				foreach ($orders as $order) {
					$order->status          = Order::STATUS_COMPLETE;
					$order->completion_time = Carbon::now();
					$order->save();
				}
			}
		})->dailyAt('1:00');
	}

}
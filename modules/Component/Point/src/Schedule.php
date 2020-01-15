<?php

namespace GuoJiangClub\Catering\Component\Point;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Scheduling\Schedule\Scheduling;
use GuoJiangClub\Catering\Component\Order\Models\OrderItemPoint;
use GuoJiangClub\Catering\Component\Point\Model\Point;

class Schedule extends Scheduling
{

	public function schedule()
	{
		//积分收货后生效
		$this->schedule->call(function () {

			$delay = app('system_setting')->getSetting('order_can_refund_day') ? app('system_setting')->getSetting('order_can_refund_day') : 7;

			//1. 首先获得所有商品销售获得积分状态为冻结的积分记录
			$orderTable     = 'el_order';
			$orderItemTable = 'el_order_item';
			$pointTable     = 'el_point';

			$points = Point::join($orderItemTable, $orderItemTable . '.id', '=', $pointTable . '.item_id')
				->join($orderTable, $orderTable . '.id', '=', $orderItemTable . '.order_id')
				->where($pointTable . '.status', 0)
				->where('el_point.action', 'order_item')
				->whereNotNull($orderTable . '.accept_time')
				->whereRaw('(DATEDIFF(now(),el_order.accept_time) >= ' . $delay . ')')
				->take(30)->select('el_point.*')->get();

			if (count($points) > 0) {
				foreach ($points as $item) {
					$point = Point::find($item->id);
					if ($order = $point->getOrder() AND $order->accept_time AND strtotime($order->accept_time) < Carbon::now()->addDay(-$delay)->timestamp) {
						$point->update(['status' => 1]);
						event('point.change', $point->user_id);
					}
				}
			}
		})->everyThirtyMinutes()->when(function () {
			return app('system_setting')->getSetting('point_apply_status');
		});
	}

}
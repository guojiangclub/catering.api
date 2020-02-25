<?php

namespace GuoJiangClub\Catering\Component\Order\Listeners;

use GuoJiangClub\Catering\Component\Discount\Actions\UnitPointTimesAction;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\EC\Catering\Notifications\PointRecord;

class OrderEventListener
{
	private $discountService;
	private $point;

	public function __construct(DiscountService $discountService, PointRepository $pointRepository
	)
	{
		$this->discountService = $discountService;
		$this->point           = $pointRepository;
	}

	public function onOrderPaid(Order $order)
	{
		if (!settings('point_enabled')) {  //如果未启用积分模块，则不进行积分操作
			return;
		}

		$items = OrderItem::where(['order_id' => $order->id])->get();

		$pointInvalidRatio = settings('point_invalid_ratio') ? settings('point_invalid_ratio') : 70;

		$point_total = 0;
		foreach ($items as $item) {

			//如果支付金额为0，并且支付金额小于吊牌价7折则不记录积分

			if (!$item->isCanGetPoint($pointInvalidRatio)) {
				continue;
			}

			//促销活动送积分
			if ($pointDiscount = $this->checkDiscountPoint($order, $item)) {

				$pointAction = $pointDiscount->getActions()->where('type', UnitPointTimesAction::TYPE)->first();

				$configuration = json_decode($pointAction->configuration, true);

				app($pointAction->type)->execute($order, $configuration, $pointDiscount);

				continue;
			}

			//正常商品获取的积分
			$point = $item->getPoint();

			if (!$point) {
				continue;
			}

			$ratio = config('point.point_rule.ratio')[$order->user->group->grade];

			$point = $point * $ratio;

			if (!Point::where('action', 'order_item')->where('item_id', $item->id)->first()) {
				//套餐是否送积分
				$no_suit_order = true;
				if ($order->type == Order::TYPE_SUIT And isset($order->specialTypes()->first()->suit->get_point) And !$order->specialTypes()->first()->suit->get_point) {
					$no_suit_order = false;
				}

				//拼团是否送积分
				$no_groupon_order = true;
				if ($order->type == Order::TYPE_GROUPON And isset($order->specialTypes()->first()->groupon_item->get_point) And !$order->specialTypes()->first()->groupon_item->get_point) {
					$no_groupon_order = false;
				}

				//秒杀是否送积分
				$no_seckill_order = true;
				if ($order->type == Order::TYPE_SECKILL And isset($order->specialTypes()->first()->seckill_item->get_point) And !$order->specialTypes()->first()->seckill_item->get_point) {
					$no_seckill_order = false;
				}

				if (!Point::where('action', 'order_item')->where('item_id', $item->id)->first() And $no_seckill_order And $no_suit_order) {


					Point::create([
						'user_id'   => $order->user_id,
						'action'    => 'order_item',
						'note'      => '购物送积分',
						'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\OrderItem',
						'item_id'   => $item->id,
						'value'     => $point,
						'status'    => 0]);

					$point_total += $point;
				}
			}

			if ($point_total > 0) {
				$user = User::find($order->user_id);
				$user->notify(new PointRecord(['point' => [
					'user_id'   => $order->user_id,
					'action'    => 'order_item',
					'note'      => '购物送积分',
					'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\OrderItem',
					'item_id'   => $item->id,
					'value'     => $point,
					'status'    => 0]]));
			}
		}
	}

	public function onOrderReceived($order)
	{
		//TODO: 之前这里进行积分操作。目前积分操作已经移入相关客户包，后续这里作为消息通知
	}

	public function onOrderItemCommented($orderItem)
	{
		$orderItem->is_commented = true;
		$orderItem->save();
	}

	public function OnOrderSubmitted($order)
	{
		if ($order->status == Order::STATUS_NEW) {

			//暂时取消这种订单自动取消的方式，避免造成job数据过多，导致队列锁死。
			/*$delayTime = settings('order_auto_cancel_time') ? settings('order_auto_cancel_time') : 1;

			$job = (new AutoCancelOrder($order))
				->delay(Carbon::now()->addMinute($delayTime));

			dispatch($job);*/
		}
	}

	public function onOrderCanceled($order_id)
	{
		$adjustments = Adjustment::where('order_id', $order_id);
		$adjustments->delete();

		$order = Order::find($order_id);

		if ($order) {
			//取消积分
			$orderItemIds = $order->items->pluck('id')->toArray();

			Point::where('item_type', 'GuoJiangClub\Catering\Component\Order\Models\OrderItem')
				->whereIn('item_id', $orderItemIds)->delete();

			if (!$this->point->findWhere(['user_id' => $order->user_id, 'action' => 'order_cancel_return_point', 'item_id' => $order->id])->first()
				AND
				$usePoint = $this->point->findWhere(['user_id' => $order->user_id, 'action' => 'order_discount', 'item_id' => $order->id])->first()
			) {
				Point::create([
					'user_id'    => $order->user_id,
					'action'     => 'order_cancel_return_point',
					'note'       => '购物取消返还积分',
					'value'      => -$usePoint->value,
					'valid_time' => $usePoint->valid_time,
					'item_type'  => 'GuoJiangClub\Catering\Component\Order\Models\Order',
					'item_id'    => $order->id,
				]);

				$user = User::find($order->user_id);
				$user->notify(new PointRecord(['point' => [
					'user_id'    => $order->user_id,
					'action'     => 'order_cancel_return_point',
					'note'       => '购物取消返还积分',
					'value'      => $usePoint->value,
					'valid_time' => $usePoint->valid_time,
					'item_type'  => 'GuoJiangClub\Catering\Component\Order\Models\Order',
					'item_id'    => $order->id,
				]]));
			}

			event('point.change', $order->user_id);
		}

		$coupon = $order->getCoupon();

		if ($coupon) {
			$coupon->used_at = null;
			$coupon->save();
		}
	}

	public function onCheckOrderCorrect(Order $order)
	{
		$items = OrderItem::where(['order_id' => $order->id])->get();

		/*更新orderItem supplier*/
		foreach ($items as $item) {
			$item->supplier_id = $item->product->goods->supplier_id;
			$item->save();
		}

		/*检测订单*/
		if (settings('order_price_protection_enabled') AND $order->type != Order::TYPE_FREE_EVENT) {

			foreach ($items as $item) {

				$product = $item->getModel();

				if ($product->is_largess) { //如果是赠品则不自动关闭订单
					continue;
				}

				$orderProtectionPercentage = settings('order_price_protection_discount_percentage')
					? settings('order_price_protection_discount_percentage') : 30;

				//如果支付金额为0，并且小于吊牌价5折则锁定订单
				if ($item->units_total == 0
					OR
					($item->units_total / $item->quantity) < ($item->getModel()->market_price * $orderProtectionPercentage)
				) {
					$order->status = Order::STATUS_INVALID;
					$order->save();
				}
			}
		}
	}

	private function checkDiscountPoint(Order $order, OrderItem $orderItem)
	{
		$adjustment = $order->adjustments()->whereIn('origin_type', ['discount', 'discount_by_market_price'])->first();

		$discounts = $this->discountService->getDiscountsByActionType(UnitPointTimesAction::TYPE);

		if (!$discounts OR count($discounts) == 0 OR !$adjustment) {
			return false;
		}

		$discount = $discounts->where('id', $adjustment->origin_id)->first();

		if (!$discount) {
			return false;
		}

		if (!app(UnitPointTimesAction::TYPE)->checkItemRule($orderItem->getModel(), $discount)) {
			return false;
		}

		return $discount;
	}

	public function subscribe($events)
	{
		$events->listen(
			'order.item.commented',
			'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener@onOrderItemCommented'
		);

		$events->listen(
			'order.submitted',
			'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener@OnOrderSubmitted'
		);

		$events->listen(
			'order.submitted',
			'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener@onCheckOrderCorrect'
		);

		$events->listen(
			'order.received',
			'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener@onOrderReceived'
		);

		$events->listen(
			'order.paid',
			'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener@onOrderPaid'
		);

		$events->listen(
			'order.canceled',
			'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener@onOrderCanceled'
		);
	}
}
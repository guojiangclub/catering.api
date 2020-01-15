<?php

namespace GuoJiangClub\Catering\Component\Order\Policies;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
	use HandlesAuthorization;

	public function update(User $user, Order $order)
	{
		return $user->id == $order->user_id;
	}

	public function submit(User $user, Order $order)
	{
		//价格保护
		if (settings('order_price_protection_enabled') AND $order->type != Order::TYPE_FREE_EVENT) {

			$items = OrderItem::where(['order_id' => $order->id])->get();

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
					return false;
				}
			}
		}

		//只有该订单属于该用户，并且订单处于临时状态下才能提交订单。
		return $user->id == $order->user_id AND $order->status == Order::STATUS_TEMP;
	}

	public function pay(User $user, Order $order)
	{
		//只有该订单属于该用户，并且订单处于待付款状态下，才能进行支付操作。
		return $user->id == $order->user_id AND $order->status == Order::STATUS_NEW;
	}

	public function cancel(User $user, Order $order)
	{
		//只有该订单属于该用户，并且订单处于待付款状态下，才能进行取消操作。
		return $user->id == $order->user_id AND $order->status == Order::STATUS_NEW;
	}

	public function received(User $user, Order $order)
	{
		return $user->id == $order->user_id AND $order->status == Order::STATUS_DELIVERED;
	}

	public function delete(User $user, Order $order)
	{
		//只有已取消订单用户才能够删除订单
		return $user->id == $order->user_id AND $order->status == Order::STATUS_CANCEL;
	}

	public function review(User $user, Order $order, OrderItem $orderItem)
	{
		//只有已收货的订单才能够进行评价商品和订单
		return $user->id == $order->user_id AND $order->id == $orderItem->order_id AND $order->status == Order::STATUS_RECEIVED;
	}

	public function refund(User $user, Order $order, OrderItem $orderItem)
	{
		$refunds = $orderItem->refunds;

		//如果已申请过2次售后,无法申请
		if ($refunds->count() == 2) {
			return false;
		}

		//如果申请了一次售后，并且未拒绝、未关闭,无法申请二次
		if ($refunds->count() == 1 AND !in_array($refunds->first()->status, [2, 4])) {
			return false;
		}

		return $user->id == $order->user_id AND $order->id == $orderItem->order_id
			AND ($order->status == Order::STATUS_RECEIVED OR
				$order->status == Order::STATUS_DELIVERED OR $order->status == Order::STATUS_PAY);
	}
}
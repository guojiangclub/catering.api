<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/5
 * Time: 14:01
 */

namespace GuoJiangClub\Catering\Core\Listeners;

use Carbon\Carbon;
use ElementVip\Component\Recharge\Models\BalanceOrder;
use ElementVip\Component\User\Models\User;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Core\Notifications\BalanceChange;
use GuoJiangClub\Catering\Core\Notifications\CouponChange;
use GuoJiangClub\Catering\Core\Notifications\PointChange;

class WechatTemplateMessageListener
{

	public function sendPointMsg($user, $note, $value)
	{
		$user->notify((new PointChange(['point' => [
			'card_no' => $user->card_no,
			'note'    => $note,
			'value'   => $value,
		]]))->delay(Carbon::now()->addSecond(10)));
	}

	public function sendBalanceMsg($order)
	{

		$user = User::find($order->user_id);
		if ($order instanceof BalanceOrder) {
			$note  = '余额储值';
			$value = $order->amount;
		} elseif ($order instanceof Order && $order->type == Order::TYPE_BALANCE) {
			$note  = '消费支出';
			$value = $order->used_balance_amount;
		} elseif ($order instanceof Order && $order->type == Order::TYPE_BALANCE_AND_POINT) {
			$note  = '消费支出';
			$value = $order->used_balance_amount;

			$user->notify((new PointChange(['point' => [
				'note'  => '订单使用积分抵扣',
				'value' => $order->redeem_point,
			]]))->delay(Carbon::now()->addSecond(10)));
		}

		$user->notify((new BalanceChange(['balance' => [
			'note'  => $note,
			'value' => $value,
			'time'  => $order->created_at,
		]]))->delay(Carbon::now()->addSecond(10)));
	}

	public function sendCouponMsg($user, $coupon)
	{
		$user->notify((new CouponChange([
			'coupon' => [
				'coupon'   => $coupon,
				'discount' => $coupon->discount->title,
			],
		]))->delay(Carbon::now()->addSecond(10)));
	}

	public function subscribe($events)
	{
		$events->listen(
			'st.wechat.message.point',
			'GuoJiangClub\Catering\Core\Listeners\WechatTemplateMessageListener@sendPointMsg'
		);

		$events->listen(
			'st.wechat.message.balance',
			'GuoJiangClub\Catering\Core\Listeners\WechatTemplateMessageListener@sendBalanceMsg'
		);

		$events->listen(
			'st.wechat.message.coupon',
			'GuoJiangClub\Catering\Core\Listeners\WechatTemplateMessageListener@sendCouponMsg'
		);
	}
}
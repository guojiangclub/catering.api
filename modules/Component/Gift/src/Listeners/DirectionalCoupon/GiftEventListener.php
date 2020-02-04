<?php

namespace GuoJiangClub\Catering\Component\Gift\Listeners\DirectionalCoupon;

class GiftEventListener
{

	public function __construct()
	{
	}

	public function subscribe($events)
	{
		$events->listen(
			'directional.coupon',
			'GuoJiangClub\Catering\Component\Gift\Listeners\DirectionalCoupon\CouponEventListener'
		);
	}
}
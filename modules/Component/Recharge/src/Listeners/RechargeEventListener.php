<?php

namespace GuoJiangClub\Catering\Component\Recharge\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;

class RechargeEventListener
{

	protected $pointRepository;

	public function __construct(PointRepository $pointRepository)
	{
		$this->pointRepository = $pointRepository;
	}

	public function subscribe($events)
	{
		$events->listen(
			'user.recharge.point',
			'GuoJiangClub\Catering\Component\Recharge\Listeners\PointEventListener'
		);

		$events->listen(
			'user.recharge.coupon',
			'GuoJiangClub\Catering\Component\Recharge\Listeners\CouponEventListener'
		);
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Gift\Listeners\Birthday;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;

class GiftEventListener
{

	protected $pointRepository;

	public function __construct(PointRepository $pointRepository)
	{
		$this->pointRepository = $pointRepository;
	}

	public function subscribe($events)
	{

		$events->listen(
			'gift.birthday.coupon',
			'GuoJiangClub\Catering\Component\Gift\Listeners\Birthday\CouponEventListener'
		);

		$events->listen(
			'gift.birthday.point',
			'GuoJiangClub\Catering\Component\Gift\Listeners\Birthday\PointEventListener'
		);
	}
}
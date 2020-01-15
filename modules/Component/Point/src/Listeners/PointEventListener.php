<?php

namespace GuoJiangClub\Catering\Component\Point\Listeners;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;

class PointEventListener
{
	private $point;

	public function __construct(PointRepository $pointRepository)
	{
		$this->point = $pointRepository;
	}

	public function onPointChange($uid)
	{
		$this->point->updateUserPoint($uid);
	}

	public function onUserLogin()
	{

	}

	public function subscribe($events)
	{
		$events->listen(
			'point.change',
			'GuoJiangClub\Catering\Component\Point\Listeners\PointEventListener@onPointChange'
		);
	}
}
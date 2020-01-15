<?php

namespace GuoJiangClub\Catering\Component\Point\Traits;

use GuoJiangClub\Catering\Component\Point\Model\Point;

trait PointTrait
{

	public function points()
	{
		return $this->hasMany(Point::class);
	}

}
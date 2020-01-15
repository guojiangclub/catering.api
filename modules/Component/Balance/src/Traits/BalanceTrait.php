<?php

namespace GuoJiangClub\Catering\Component\Balance\Traits;

use GuoJiangClub\Catering\Component\Balance\Model\Balance;

trait BalanceTrait
{
	public function balance()
	{
		return $this->morphMany(Balance::class, 'origin');
	}

}


<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;

class UsageLimitEligibilityChecker
{
	/**
	 * @param Discount $discount
	 *
	 * @return bool
	 */
	public function isEligible(DiscountContract $discount)
	{
		if (null === $usageLimit = $discount->usage_limit) {
			return true;
		}

		if ($discount->used <= $usageLimit) {
			return true;
		}

		return false;
	}
}

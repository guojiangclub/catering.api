<?php

namespace GuoJiangClub\Catering\Server\Applicator;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;

class DiscountApplicator
{
	public function apply(DiscountSubjectContract $subject, DiscountContract $discount)
	{

		$action = $discount->getActions();
		if (!is_null($action)) {

			$configuration = json_decode($action->configuration, true);

			$result = app($action->type)->execute($subject, $configuration, $discount);
			if ($result) {
				$discount->setCouponUsed();
			}
		}
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\RuleCheckerContract;

class ItemTotalRuleChecker implements RuleCheckerContract
{
	const TYPE = 'item_total';

	public function isEligible(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		return $subject->getCurrentTotal() >= $configuration['amount'];
	}
}
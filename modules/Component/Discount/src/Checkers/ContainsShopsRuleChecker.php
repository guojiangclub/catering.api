<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\RuleCheckerContract;

class ContainsShopsRuleChecker implements RuleCheckerContract
{
	const TYPE = 'contains_shops';

	public function isEligible(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		if ($subject->channel != 'shop') {
			return false;
		}

		if (!isset($configuration['shop_id']) || !$configuration['shop_id']) {
			return false;
		}

		$shops_id = explode(',', $configuration['shop_id']);
		if (empty($shops_id) || !in_array($subject->channel_id, $shops_id)) {
			return false;
		}

		return true;
	}

	public function isEligibleByItem($shop_id, array $configuration)
	{
		$shops_id = explode(',', $configuration['shop_id']);
		if ($shop_id AND in_array($shop_id, $shops_id)) {
			return true;
		}

		return false;
	}
}
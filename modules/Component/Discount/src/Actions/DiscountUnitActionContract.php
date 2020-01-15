<?php

namespace GuoJiangClub\Catering\Component\Discount\Actions;

use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsCategoryRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsProductRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountActionContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Component\Discount\Models\Rule;

abstract class DiscountUnitActionContract implements DiscountActionContract
{
	public function checkItemRule(DiscountItemContract $subjectItem, DiscountContract $discount)
	{

		if (!$discount->hasRules()) {
			return true;
		}

		foreach ($discount->getRules()->whereIn('type', [ContainsCategoryRuleChecker::TYPE, ContainsProductRuleChecker::TYPE]) as $rule) {
			if (!$this->isEligibleToRule($subjectItem, $rule)) {
				return false;
			}
		}

		return true;
	}

	protected function isEligibleToRule(DiscountItemContract $subjectItem, Rule $rule)
	{
		$checker = app($rule->type);

		$configuration = json_decode($rule->configuration, true);

		return $checker->isEligibleByItem($subjectItem, $configuration);
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Models\Rule;

class RulesEligibilityChecker
{
	/**
	 * {@inheritdoc}
	 */
	public function isEligible(DiscountSubjectContract $subject, DiscountContract $discount)
	{
		if (!$discount->hasRules()) {
			return true;
		}

		foreach ($discount->getRules() as $rule) {
			if (!$this->isEligibleToRule($subject, $rule, $discount)) {
				return false;
			}
		}

		return true;
	}

	protected function isEligibleToRule(DiscountSubjectContract $subject, Rule $rule, DiscountContract $discountContract)
	{
		$checker = app($rule->type);

		$configuration = json_decode($rule->configuration, true);

		return $checker->isEligible($subject, $configuration, $discountContract);
	}
}

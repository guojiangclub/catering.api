<?php

namespace GuoJiangClub\Catering\Component\Discount\Applicators;

use GuoJiangClub\Catering\Component\Discount\Checkers\ItemTotalRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;

class DiscountApplicator
{
	public function apply(DiscountSubjectContract $subject, DiscountContract $discount)
	{
		if (count($discount->getActions())) {

			foreach ($discount->getActions() as $action) {

				$configuration = json_decode($action->configuration, true);

				app($action->type)->execute($subject, $configuration, $discount);

				$discount->setCouponUsed();
			}
		}
	}

	public function calculate(DiscountSubjectContract $subject, DiscountContract $discount)
	{

		$discount->orderAmountLimit = 0;

		if (count($discount->getActions())) {

			foreach ($discount->getActions() as $action) {

				$configuration = json_decode($action->configuration, true);
				app($action->type)->calculate($subject, $configuration, $discount);
			}
		}

		foreach ($discount->getRules() as $rule) {
			if ($rule->type == ItemTotalRuleChecker::TYPE) {
				$discount->orderAmountLimit = json_decode($rule->configuration, true)['amount'];
			}
		}
	}

	public function combinationCalculate(DiscountSubjectContract $subject, DiscountContract $discount)
	{

		$discount->orderAmountLimit = 0;

		if (count($discount->getActions())) {

			foreach ($discount->getActions() as $action) {

				$configuration = json_decode($action->configuration, true);
				app($action->type)->combinationCalculate($subject, $configuration, $discount);
			}
		}

		foreach ($discount->getRules() as $rule) {
			if ($rule->type == ItemTotalRuleChecker::TYPE) {
				$discount->orderAmountLimit = json_decode($rule->configuration, true)['amount'];
			}
		}
	}

}
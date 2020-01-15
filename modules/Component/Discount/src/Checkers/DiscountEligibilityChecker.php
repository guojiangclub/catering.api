<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;

class DiscountEligibilityChecker
{
	protected $datesEligibilityChecker;

	protected $usageLimitEligibilityChecker;

	protected $rulesEligibilityChecker;

	public function __construct(
		DatesEligibilityChecker $datesEligibilityChecker,
		UsageLimitEligibilityChecker $usageLimitEligibilityChecker,
		RulesEligibilityChecker $rulesEligibilityChecker
	)
	{
		$this->datesEligibilityChecker      = $datesEligibilityChecker;
		$this->usageLimitEligibilityChecker = $usageLimitEligibilityChecker;
		$this->rulesEligibilityChecker      = $rulesEligibilityChecker;
	}

	public function isEligible(DiscountSubjectContract $subject, DiscountContract $discount)
	{
		/*$discount->orderAmountLimit = 0;*/

		if (!$this->datesEligibilityChecker->isEligible($discount)) {
			return false;
		}

		if (!$this->usageLimitEligibilityChecker->isEligible($discount)) {
			return false;
		}

		$eligible = $this->rulesEligibilityChecker->isEligible($subject, $discount);

		if (!$eligible) {
			return false;
		}

		return $eligible;
	}

	public function isEligibleItem(DiscountItemContract $item, DiscountContract $discount)
	{

		if (!$this->datesEligibilityChecker->isEligible($discount)) {
			return false;
		}

		if (!$this->usageLimitEligibilityChecker->isEligible($discount)) {
			return false;
		}

		if (!$discount->hasRules()) {
			return true;
		}

		//包含角色的促销一律不展示，因为一般是特供渠道
		if ($discount->getRules()->where('type', 'contains_role')->first()) {
			return false;
		}

		//如果包含了这三种规则，则需要判断
		foreach ($discount->getRules()->whereIn('type', ['contains_category', 'contains_product', 'contains_wechat_group', 'contains_market_shop', 'exclude_market_shop', 'contains_market']) as $rule) {
			$checker       = app($rule->type);
			$configuration = json_decode($rule->configuration, true);
			if ($checker->isEligibleByItem($item, $configuration)) {
				return true;
			} else {
				return false;
			}
		}

		return true;
	}

	public function isEligibleItemForO2o(DiscountItemContract $item, DiscountContract $discount)
	{

		if (!$this->datesEligibilityChecker->isEligible($discount)) {
			return false;
		}

		if (!$this->usageLimitEligibilityChecker->isEligible($discount)) {
			return false;
		}

		if (!$discount->hasRules()) {
			return true;
		}

		//包含角色的促销一律不展示，因为一般是特供渠道
		if ($discount->getRules()->where('type', 'contains_role')->first()) {
			return false;
		}

		//如果包含了这两种规则，则需要判断
		$flag_shop     = true;
		$flag_category = true;
		$flag_product  = true;
		foreach ($discount->getRules()->whereIn('type', ['contains_category', 'contains_product', 'contains_shops']) as $rule) {
			$checker       = app($rule->type);
			$configuration = json_decode($rule->configuration, true);
			if ($rule->type == 'contains_shops' AND !$checker->isEligibleByItem(request('shop_id'), $configuration)) {
				$flag_shop = false;
			}
			if ($rule->type == 'contains_category' AND !$checker->isEligibleByItem($item, $configuration)) {
				$flag_category = false;
			}
			if ($rule->type == 'contains_product' AND !$checker->isEligibleByItem($item, $configuration)) {
				$flag_product = false;
			}
		}
		//只有适应当前门店，并且category或product校验通过
		if ($flag_shop AND ($flag_category OR $flag_product)) {
			return true;
		}

		return false;
	}
}

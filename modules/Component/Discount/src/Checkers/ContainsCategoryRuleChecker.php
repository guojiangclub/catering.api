<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\RuleCheckerContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Component\Category\Models\Category;
use Illuminate\Support\Collection;

class ContainsCategoryRuleChecker implements RuleCheckerContract
{
	const TYPE = 'contains_category';

	public function isEligible(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$flag       = false;
		$validItems = new Collection();

		foreach ($subject->getItems() as $item) {
			if (isset($configuration['exclude_spu']) AND in_array($item->getItemKey('spu'), explode(',', $configuration['exclude_spu']))) {
				continue;
			}

			$ids = $item->getModel()->getCategories()->pluck('id')->intersect($configuration['items']);
			if ($ids AND $ids->count() > 0) {
				$validItems->push($item);
			}
		}

		if ($validItems->count() > 0) {
			//1. 说明只要有一件商品满足规则，就当满足条件
			$flag = true;
		} else {
			return false;
		}

		//2. 其他条件检查

		/*if ($cartQuantityRule = $discount->getRules()->where('type', CartQuantityRuleChecker::TYPE)->first()) {
			$count = $cartQuantityRule->getCartQuantity();
			if ( $count > 0 AND $count > $validItems->sum('quantity')) {
				$flag = false;
			}
		}

		if ($itemTotalRule = $discount->getRules()->where('type', ItemTotalRuleChecker::TYPE)->first()) {
			$amount = $itemTotalRule->getItemsTotal();
			if ($amount  > 0 AND $amount > $validItems->sum('units_total')) {
				$flag = false;
			}
		}*/

		return $flag;
	}

	public function isEligibleByItem(DiscountItemContract $item, array $configuration)
	{
		if (isset($configuration['exclude_spu']) AND in_array($item->getKeyCode('spu'), explode(',', $configuration['exclude_spu']))) {
			return false;
		}

		$ids = $item->getCategories()->pluck('id')->intersect($configuration['items']);
		if ($ids AND $ids->count() > 0) {
			return true;
		}

		return false;
	}

}
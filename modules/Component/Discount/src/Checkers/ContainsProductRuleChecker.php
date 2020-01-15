<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\RuleCheckerContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Component\Discount\Models\Rule;
use Illuminate\Support\Collection;

class ContainsProductRuleChecker implements RuleCheckerContract
{
	const TYPE = 'contains_product';

	public function isEligible(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$flag       = false;
		$validItems = new Collection();

		foreach ($subject->getItems() as $item) {
			if (isset($configuration['sku']) AND !empty($configuration['sku']) AND in_array($item->getItemKey(), explode(',', $configuration['sku']))) {
				$validItems->push($item);
			}
			if (isset($configuration['spu']) AND !empty($configuration['spu']) AND in_array($item->getItemKey('spu'), explode(',', $configuration['spu']))) {
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
		if ($cartQuantityRule = $discount->getRules()->where('type', CartQuantityRuleChecker::TYPE)->first()) {
			$count = $cartQuantityRule->getCartQuantity();
			if ($count > 0 AND $count > $validItems->sum('quantity')) {
				$flag = false;
			}
		}

		if ($itemTotalRule = $discount->getRules()->where('type', ItemTotalRuleChecker::TYPE)->first()) {
			$amount = $itemTotalRule->getItemsTotal();
			if ($amount > 0 AND $amount > $validItems->sum('units_total')) {
				$flag = false;
			}
		}

		return $flag;
	}

	public function isEligibleByItem(DiscountItemContract $item, array $configuration)
	{
		if ($item->getItemType() == 'goods') {
			if (isset($configuration['spu']) AND !empty($configuration['spu']) AND in_array($item->getKeyCode('spu'), explode(',', $configuration['spu']))) {
				return true;
			}
			$codes = $item->getChildKeyCodes();
			foreach ($codes as $code) {
				if (isset($configuration['sku']) AND !empty($configuration['sku']) AND in_array($code, explode(',', $configuration['sku']))) {
					return true;
				}
			}
		}
		if ($item->getItemType() == 'product') {
			if ((isset($configuration['sku']) AND !empty($configuration['sku']) AND in_array($item->getKeyCode(), explode(',', $configuration['sku'])))
				OR (isset($configuration['spu']) AND !empty($configuration['spu']) AND in_array($item->getKeyCode('spu'), explode(',', $configuration['spu'])))
			) {
				return true;
			}
		}

		return false;
	}
}
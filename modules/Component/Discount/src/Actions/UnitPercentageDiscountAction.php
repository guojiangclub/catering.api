<?php

namespace GuoJiangClub\Catering\Component\Discount\Actions;

use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsCategoryRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsProductRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountActionContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use Illuminate\Support\Collection;

class UnitPercentageDiscountAction extends DiscountUnitActionContract
{
	const TYPE = 'goods_percentage_discount';

	public function execute(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		foreach ($subject->getItems() as $item) {
			if ($this->checkItemRule($item->getModel(), $discount)) { //只有符合规则的商品才能获得积分

				$discountAmount            = -1 * (int) round($item->total * (1 - $configuration['percentage'] / 100));
				$adjustment                = $this->createAdjustment($discount);
				$adjustment->amount        = $discountAmount;
				$adjustment->order_item_id = $item->id;
				$subject->addAdjustment($adjustment);

				$item->item_discount += $discountAmount;
				$item->recalculateAdjustmentsTotal();
			}
			/*if ($rule = $discount->getRules()->where('type', ContainsProductRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if ((isset($ruleConfiguration['sku']) AND !empty($ruleConfiguration['sku']) AND in_array($item->getItemKey(), explode(',', $ruleConfiguration['sku'])))
					OR (isset($ruleConfiguration['spu']) AND !empty($ruleConfiguration['spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['spu'])))
				){
					$discountAmount = -1 * (int)round(($item->units_total / $item->quantity) * (1 - $configuration['percentage'] / 100));
					$adjustment = $this->createAdjustment($discount);
					$adjustment->amount = $discountAmount;
					$adjustment->order_item_id = $item->id;
					$subject->addAdjustment($adjustment);

					$item->adjustments_total = $discountAmount;
					$item->total = $item->units_total +  $item->adjustments_total;
				}
			}
			if ($rule = $discount->getRules()->where('type', ContainsCategoryRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				$ids = $item->getModel()->getCategories()->pluck('id')->intersect($ruleConfiguration['items']);
				if ($ids AND $ids->count() > 0) {
					$discountAmount = -1 * (int)round(($item->units_total / $item->quantity) * (1 - $configuration['percentage'] / 100));
					$adjustment = $this->createAdjustment($discount);
					$adjustment->amount = $discountAmount;
					$adjustment->order_item_id = $item->id;
					$subject->addAdjustment($adjustment);
				}
			}*/
		}
	}

	private function createAdjustment(DiscountContract $discount)
	{
		if ($discount->isCouponBased()) {
			$adjustment = new Adjustment(['type'    => Adjustment::ORDER_ITEM_DISCOUNT_ADJUSTMENT
			                              , 'label' => $discount->label, 'origin_type' => 'coupon', 'origin_id' => $discount->id]);

			return $adjustment;
		}
		$adjustment = new Adjustment(['type'    => Adjustment::ORDER_ITEM_DISCOUNT_ADJUSTMENT
		                              , 'label' => $discount->label, 'origin_type' => 'discount', 'origin_id' => $discount->id]);

		return $adjustment;
	}

	public function calculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$discount->adjustments = new Collection();

		$adjustmentTotal = 0;

		foreach ($subject->getItems() as $item) {

			if ($this->checkItemRule($item->getModel(), $discount)) { //只有符合规则的商品才能获得积分

				$discountAmount = -1 * (int) round($item->total * (1 - $configuration['percentage'] / 100));
				$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);

				$adjustmentTotal += $discountAmount;
			}
			/*if ($rule = $discount->getRules()->where('type', ContainsProductRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if ((isset($ruleConfiguration['sku']) AND !empty($ruleConfiguration['sku']) AND in_array($item->getItemKey(), explode(',', $ruleConfiguration['sku'])))
					OR (isset($ruleConfiguration['spu']) AND !empty($ruleConfiguration['spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['spu'])))
				) {
					$discountAmount = -1 * (int)round(($item->units_total / $item->quantity) * (1 - $configuration['percentage'] / 100));
					$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
					$adjustmentTotal += $discountAmount;
				}
			}

			if ($rule = $discount->getRules()->where('type', ContainsCategoryRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				$ids = $item->getModel()->getCategories()->pluck('id')->intersect($ruleConfiguration['items']);
				if ($ids AND $ids->count() > 0) {
					$discountAmount = -1 * (int)round(($item->units_total / $item->quantity) * (1 - $configuration['percentage'] / 100));
					$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
					$adjustmentTotal += $discountAmount;
				}
			}*/
		}
		$discount->adjustmentTotal = $adjustmentTotal;
		/*$subject->adjustments_total = $adjustmentTotal;
		$subject->recalculateTotal();*/
	}

	public function combinationCalculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$discount->adjustments = new Collection();

		$adjustmentTotal = 0;

		foreach ($subject->getItems() as $item) {

			if ($this->checkItemRule($item->getModel(), $discount)) { //只有符合规则的商品才能获得积分

				$discountAmount = -1 * (int) round($item->total * (1 - $configuration['percentage'] / 100));

				/*$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);*/

				$adjustmentTotal += $discountAmount;

				$item->adjustments_total = $discountAmount;
				$item->total             = $item->units_total + $item->adjustments_total;
			}
			/*if ($rule = $discount->getRules()->where('type', ContainsProductRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if ((isset($ruleConfiguration['sku']) AND !empty($ruleConfiguration['sku']) AND in_array($item->getItemKey(), explode(',', $ruleConfiguration['sku'])))
					OR (isset($ruleConfiguration['spu']) AND !empty($ruleConfiguration['spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['spu'])))
				) {
					$discountAmount = -1 * (int)round(($item->units_total / $item->quantity) * (1 - $configuration['percentage'] / 100));
					$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
					$adjustmentTotal += $discountAmount;
				}
			}

			if ($rule = $discount->getRules()->where('type', ContainsCategoryRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				$ids = $item->getModel()->getCategories()->pluck('id')->intersect($ruleConfiguration['items']);
				if ($ids AND $ids->count() > 0) {
					$discountAmount = -1 * (int)round(($item->units_total / $item->quantity) * (1 - $configuration['percentage'] / 100));
					$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
					$adjustmentTotal += $discountAmount;
				}
			}*/
		}

		$discount->adjustmentTotal = $adjustmentTotal;

		$subject->adjustments_total = $adjustmentTotal;
		$subject->recalculateTotal();
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Discount\Actions;

use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsCategoryRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsProductRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountActionContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use Illuminate\Support\Collection;

class UnitPercentageByMarketPriceDiscountAction implements DiscountActionContract
{
	const TYPE = 'goods_percentage_by_market_price_discount';

	public function execute(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		foreach ($subject->getItems() as $item) {

			if ($rule = $discount->getRules()->where('type', ContainsProductRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if (isset($ruleConfiguration['sku']) AND !empty($ruleConfiguration['sku']) AND in_array($item->getItemKey(), explode(',', $ruleConfiguration['sku']))) {
					$model = $item->getModel();
					if ($model AND $goods = $model->goods) {
						$discountAmount            = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$adjustment                = $this->createAdjustment($discount);
						$adjustment->amount        = $discountAmount;
						$adjustment->order_item_id = $item->id;
						$subject->addAdjustment($adjustment);
						$item->adjustments_total = $discountAmount;
						$item->total             = $item->units_total + $item->adjustments_total;
					}
				} elseif (isset($ruleConfiguration['spu']) AND !empty($ruleConfiguration['spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['spu']))) {
					$model = $item->getModel();
					if ($model) {
						$discountAmount            = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$adjustment                = $this->createAdjustment($discount);
						$adjustment->amount        = $discountAmount;
						$adjustment->order_item_id = $item->id;
						$subject->addAdjustment($adjustment);
						$item->adjustments_total = $discountAmount;
						$item->total             = $item->units_total + $item->adjustments_total;
					}
				}
			}
			if ($rule = $discount->getRules()->where('type', ContainsCategoryRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if (isset($ruleConfiguration['exclude_spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['exclude_spu']))) {
					continue;
				}

				$ids = $item->getModel()->getCategories()->pluck('id')->intersect($ruleConfiguration['items']);
				if ($ids AND $ids->count() > 0) {
					$model = $item->getModel();
					if ($model AND $item->type == 'GuoJiangClub\Catering\Component\Product\Models\Goods') {
						$discountAmount            = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$adjustment                = $this->createAdjustment($discount);
						$adjustment->amount        = $discountAmount;
						$adjustment->order_item_id = $item->id;
						$subject->addAdjustment($adjustment);

						$item->item_discount += $discountAmount;
						$item->recalculateAdjustmentsTotal();
					} elseif ($model AND $goods = $model->goods) {
						$discountAmount            = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$adjustment                = $this->createAdjustment($discount);
						$adjustment->amount        = $discountAmount;
						$adjustment->order_item_id = $item->id;
						$subject->addAdjustment($adjustment);

						$item->item_discount += $discountAmount;
						$item->recalculateAdjustmentsTotal();
					}
				}
			}
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
		                              , 'label' => $discount->label, 'origin_type' => 'discount_by_market_price', 'origin_id' => $discount->id]);

		return $adjustment;
	}

	public function calculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$discount->adjustments = new Collection();

		$adjustmentTotal = 0;

		foreach ($subject->getItems() as $item) {

			if ($rule = $discount->getRules()->where('type', ContainsProductRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if (isset($ruleConfiguration['sku']) AND !empty($ruleConfiguration['sku']) AND in_array($item->getItemKey(), explode(',', $ruleConfiguration['sku']))) {
					$model = $item->getModel();
					if ($model AND $goods = $model->goods) {
						$discountAmount = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
						$adjustmentTotal += $discountAmount;
					}
				} elseif (isset($ruleConfiguration['spu']) AND !empty($ruleConfiguration['spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['spu']))) {
					$model = $item->getModel();
					if ($model) {
						$discountAmount = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
						$adjustmentTotal += $discountAmount;
					}
				}
			}

			if ($rule = $discount->getRules()->where('type', ContainsCategoryRuleChecker::TYPE)->first()) {

				$ruleConfiguration = json_decode($rule->configuration, true);

				if (isset($ruleConfiguration['exclude_spu']) AND in_array($item->getItemKey('spu'), explode(',', $ruleConfiguration['exclude_spu']))) {
					continue;
				}

				$ids = $item->getModel()->getCategories()->pluck('id')->intersect($ruleConfiguration['items']);
				if ($ids AND $ids->count() > 0) {
					$model = $item->getModel();
					if ($model AND $item->type == 'GuoJiangClub\Catering\Component\Product\Models\Goods') {
						$discountAmount = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
						$adjustmentTotal += $discountAmount;
					} elseif ($model AND $goods = $model->goods) {
						$discountAmount = -1 * (int) round(($model->market_price * 100 * $item->quantity) * (1 - $configuration['percentage'] / 100));
						$discount->adjustments->push(['order_id' => $subject->id, 'order_item_id' => $item->id, 'amount' => $discountAmount]);
						$adjustmentTotal += $discountAmount;
					}
				}
			}
		}

		$discount->adjustmentTotal = $adjustmentTotal;
	}

	public function combinationCalculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		// TODO: Implement combinationCalculate() method.
	}
}
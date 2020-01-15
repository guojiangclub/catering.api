<?php

namespace GuoJiangClub\Catering\Component\Discount\Actions;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountActionContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;

class UnitPointTimesAction extends DiscountUnitActionContract
{
	const TYPE = 'goods_times_point';

	public function execute(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		if (!$subject->isPaid()) { //未支付不生成冻结积分
			$adjustment         = $this->createAdjustment($discount);
			$adjustment->amount = 0;
			$subject->addAdjustment($adjustment);

			return;
		}

		$pointInvalidRatio = settings('point_invalid_ratio') ? settings('point_invalid_ratio') : 70;

		foreach ($subject->getItems() as $item) {

			if ($item->units_total == 0
				OR ($item->total / $item->quantity) < ($item->getModel()->market_price * $pointInvalidRatio)
			) {
				continue;
			}

			if ($this->checkItemRule($item->getModel(), $discount)) { //只有符合规则的商品才能获得积分

				$point = ($item->total / 100) * ($configuration['percentage'] / 100);

				if (!Point::where('action', 'order_item')->where('item_id', $item->id)->first()) {
					Point::create([
						'user_id'   => $subject->getSubjectUser()->id,
						'action'    => 'order_item',
						'note'      => '购物送积分',
						'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\OrderItem',
						'item_id'   => $item->id,
						'value'     => $point,
						'status'    => 0]);
				}
			}
		}
	}

	private function createAdjustment(DiscountContract $discount)
	{
		if ($discount->isCouponBased()) {
			$adjustment = new Adjustment(['type'    => Adjustment::ORDER_DISCOUNT_ADJUSTMENT
			                              , 'label' => $discount->label, 'origin_type' => 'coupon', 'origin_id' => $discount->id]);

			return $adjustment;
		}
		$adjustment = new Adjustment(['type'    => Adjustment::ORDER_DISCOUNT_ADJUSTMENT
		                              , 'label' => $discount->label, 'origin_type' => 'discount', 'origin_id' => $discount->id]);

		return $adjustment;
	}

	public function calculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		// TODO: Implement calculate() method.
	}

	public function combinationCalculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		// TODO: Implement combinationCalculate() method.
	}
}
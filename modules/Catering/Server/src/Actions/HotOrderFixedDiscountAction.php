<?php

namespace GuoJiangClub\Catering\Server\Actions;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountActionContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;

class HotOrderFixedDiscountAction implements DiscountActionContract
{
	const TYPE = 'hot_order_fixed_discount';

	public function execute(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$discountAmount = $this->calculateAdjustmentAmount($subject->getCurrentTotal(), $configuration['amount']);

		if (0 === $discountAmount) {
			return false;
		}

		$adjustment         = $this->createAdjustment($discount);
		$adjustment->amount = $discountAmount;
		$subject->addAdjustment($adjustment);

		return true;
	}

	private function calculateAdjustmentAmount($discountSubjectTotal, $targetDiscountAmount)
	{
		return -1 * min($discountSubjectTotal, $targetDiscountAmount);
	}

	private function createAdjustment(DiscountContract $discount)
	{
		if ($discount->isCouponBased()) {
			$adjustment = new Adjustment([
				'type'        => Adjustment::ORDER_DISCOUNT_ADJUSTMENT,
				'label'       => $discount->label,
				'origin_type' => 'coupon',
				'origin_id'   => $discount->id,
			]);

			return $adjustment;
		}

		$adjustment = new Adjustment([
			'type'        => Adjustment::ORDER_DISCOUNT_ADJUSTMENT,
			'label'       => $discount->label,
			'origin_type' => 'discount',
			'origin_id'   => $discount->id,
		]);

		return $adjustment;
	}

	public function calculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{

	}

	public function combinationCalculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{

	}
}
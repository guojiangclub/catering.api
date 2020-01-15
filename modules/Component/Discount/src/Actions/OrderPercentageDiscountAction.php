<?php

namespace GuoJiangClub\Catering\Component\Discount\Actions;

use GuoJiangClub\Catering\Component\Discount\Applicators\UnitsDiscountAdjustmentsApplicator;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountActionContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Distributors\IntegerDistributor;
use GuoJiangClub\Catering\Component\Discount\Distributors\ProportionalIntegerDistributor;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use Illuminate\Support\Collection;

class OrderPercentageDiscountAction implements DiscountActionContract
{

	const TYPE = 'order_percentage_discount';

	private $integerDistributor;
	private $unitsPromotionAdjustmentsApplicator;
	private $distributor;

	public function __construct(IntegerDistributor $integerDistributor
		, UnitsDiscountAdjustmentsApplicator $unitsDiscountAdjustmentsApplicator
		, IntegerDistributor $distributor)
	{
		$this->integerDistributor                  = $integerDistributor;
		$this->unitsPromotionAdjustmentsApplicator = $unitsDiscountAdjustmentsApplicator;
		$this->distributor                         = $distributor;
	}

	public function execute(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$discountAmount = $this->calculateAdjustmentAmount($subject->getCurrentTotal(), $configuration['percentage']);
		if (0 === $discountAmount) {
			return;
		}

		$adjustment         = $this->createAdjustment($discount);
		$adjustment->amount = $discountAmount;
		$subject->addAdjustment($adjustment);

//        $splitDiscountAmount = $this->distributor->distribute($discountAmount, $subject->countItems());
		$splitDiscountAmount = $this->distributor->distributePercentage($discountAmount, $subject);

		$i = 0;
		foreach ($subject->getItems() as $item) {
			$item->divide_order_discount += $splitDiscountAmount[$i++];
			$item->recalculateAdjustmentsTotal();
		}
	}

	private function calculateAdjustmentAmount($discountSubjectTotal, $percentage)
	{
		return -1 * (int) round($discountSubjectTotal * (1 - $percentage / 100));
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
		$discount->adjustments = new Collection();

		$discountAmount = $this->calculateAdjustmentAmount($subject->getCurrentTotal(), $configuration['percentage']);

		$discount->adjustments->push(['order_id' => $subject->id, 'amount' => $discountAmount]);

		$discount->adjustmentTotal = $discountAmount;

		return $discountAmount;
	}

	public function combinationCalculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount)
	{
		$discountAmount = $this->calculate($subject, $configuration, $discount);

		$splitDiscountAmount = $this->distributor->distributePercentage($discountAmount, $subject);

		$i = 0;
		foreach ($subject->getItems() as $item) {
			$item->adjustments_total = $splitDiscountAmount[$i++];
			$item->total             = $item->units_total + $item->adjustments_total;
		}

		$subject->adjustments_total = $discountAmount;
		$subject->recalculateTotal();
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Discount\Applicators;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Distributors\IntegerDistributor;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use GuoJiangClub\Catering\Component\Order\Models\OrderItemUnit;
use Webmozart\Assert\Assert;

class UnitsDiscountAdjustmentsApplicator
{
	private $distributor;

	public function __construct(
		IntegerDistributor $distributor
	)
	{
		$this->distributor = $distributor;
	}

	public function apply(DiscountSubjectContract $order, DiscountContract $discountContract, array $adjustmentsAmounts)
	{
		Assert::eq($order->countItems(), count($adjustmentsAmounts));

		$i = 0;
		foreach ($order->getItems() as $item) {
			$adjustmentAmount = $adjustmentsAmounts[$i++];
			if (0 === $adjustmentAmount) {
				continue;
			}

			$this->applyAdjustmentsOnItemUnits($item, $discountContract, $adjustmentAmount);
		}
	}

	private function applyAdjustmentsOnItemUnits(OrderItem $item, DiscountContract $discount, $itemDiscountAmount)
	{
		$splitDiscountAmount = $this->distributor->distribute($itemDiscountAmount, $item->quantity);

		$i = 0;
		foreach ($item->units as $unit) {
			$discountAmount = $splitDiscountAmount[$i++];
			if (0 === $discountAmount) {
				continue;
			}

			$this->addAdjustment($discount, $unit, $discountAmount);
		}
	}

	private function addAdjustment(DiscountContract $discount, OrderItemUnit $unit, $amount)
	{
		//TODO: add adjustment to unit;
	}
}

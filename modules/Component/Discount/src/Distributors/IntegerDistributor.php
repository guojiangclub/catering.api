<?php

namespace GuoJiangClub\Catering\Component\Discount\Distributors;

class IntegerDistributor
{

	public function distributePercentage($amount, $subject)
	{
		$itemsTotals = [];
		foreach ($subject->getItems() as $item) {
			$itemsTotals[] = $item->getTotal();
		}

		$total              = array_sum($itemsTotals);
		$distributedAmounts = [];

		foreach ($itemsTotals as $element) {
			$distributedAmounts[] = (int) round(($element * $amount) / $total, 0, PHP_ROUND_HALF_DOWN);
		}

		$missingAmount = $amount - array_sum($distributedAmounts);
		for ($i = 0; $i < abs($missingAmount); $i++) {
			$distributedAmounts[$i] += $missingAmount >= 0 ? 1 : -1;
		}

		return $distributedAmounts;
	}

	public function distribute($amount, $numberOfTargets)
	{
		if (!$this->validateNumberOfTargets($numberOfTargets)) {
			throw new \InvalidArgumentException('数量必须为整数类型，并且大于 0');
		}

		$sign   = $amount < 0 ? -1 : 1;
		$amount = abs($amount);

		$low  = (int) ($amount / $numberOfTargets);
		$high = $low + 1;

		$remainder = $amount % $numberOfTargets;
		$result    = [];

		for ($i = 0; $i < $remainder; ++$i) {
			$result[] = $high * $sign;
		}

		for ($i = $remainder; $i < $numberOfTargets; ++$i) {
			$result[] = $low * $sign;
		}

		return $result;
	}

	private function validateNumberOfTargets($numberOfTargets)
	{
		return is_int($numberOfTargets) && 1 <= $numberOfTargets;
	}
}

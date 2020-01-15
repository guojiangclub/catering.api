<?php

namespace GuoJiangClub\Catering\Component\Discount\Distributors;

use Webmozart\Assert\Assert;

class ProportionalIntegerDistributor
{

	public function distribute(array $integers, $amount)
	{
		Assert::allInteger($integers);
		Assert::integer($amount);

		$total              = array_sum($integers);
		$distributedAmounts = [];

		foreach ($integers as $element) {
			$distributedAmounts[] = (int) round(($element * $amount) / $total, 0, PHP_ROUND_HALF_DOWN);
		}

		$missingAmount = $amount - array_sum($distributedAmounts);
		for ($i = 0; $i < abs($missingAmount); $i++) {
			$distributedAmounts[$i] += $missingAmount >= 0 ? 1 : -1;
		}

		return $distributedAmounts;
	}
}

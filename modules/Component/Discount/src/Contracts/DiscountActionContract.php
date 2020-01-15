<?php

namespace GuoJiangClub\Catering\Component\Discount\Contracts;

interface DiscountActionContract
{
	public function execute(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount);

	public function calculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount);

	public function combinationCalculate(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount);
}
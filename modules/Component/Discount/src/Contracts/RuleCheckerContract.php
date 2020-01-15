<?php

namespace GuoJiangClub\Catering\Component\Discount\Contracts;

interface RuleCheckerContract
{
	public function isEligible(DiscountSubjectContract $subject, array $configuration, DiscountContract $discount);

}

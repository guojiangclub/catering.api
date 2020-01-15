<?php

namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountSubjectContract;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;

class CouponEligibilityChecker
{
	protected $rulesEligibilityChecker;

	public function __construct(
		RulesEligibilityChecker $rulesEligibilityChecker
	)
	{
		$this->rulesEligibilityChecker = $rulesEligibilityChecker;
	}

	public function isEligible(DiscountSubjectContract $subject, Coupon $coupon)
	{
		/*$coupon->orderAmountLimit = 0;*/

		if ($coupon->discount->type == 1 && $coupon->discount->channel == 'ec') { //线下优惠券不需要展示给线上
			return false;
		}

		//如果优惠券还没有到使用时间，也不能够进行下单
		if ($coupon->discount->usestart_at > Carbon::now()) {
			return false;
		}

		$eligible = $this->rulesEligibilityChecker->isEligible($subject, $coupon->discount);

		/*$coupon->orderAmountLimit = $coupon->discount->orderAmountLimit;*/

		if (!$eligible) {
			return false;
		}

		return $eligible;
	}
}
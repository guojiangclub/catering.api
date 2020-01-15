<?php
namespace GuoJiangClub\Catering\Component\Discount\Checkers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;

class DatesEligibilityChecker 
{
    /**
     * @param Discount $discount
     * @return bool
     */
    public function isEligible(DiscountContract $discount)
    {
        $now = Carbon::now();

        $startsAt = $discount->starts_at;
        if (null !== $startsAt && $now < $startsAt) {
            return false;
        }

        $endsAt = $discount->ends_at;
        if (null !== $endsAt && $now > $endsAt) {
            return false;
        }

        return true;
    }
}

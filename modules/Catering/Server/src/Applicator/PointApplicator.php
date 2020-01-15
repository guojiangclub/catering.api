<?php

namespace GuoJiangClub\Catering\Server\Applicator;

use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Component\Point\Contract\PointSubjectContract;
use GuoJiangClub\Catering\Component\Point\Applicator\PointApplicator as BasePointApplicator;

class PointApplicator extends BasePointApplicator
{
	public function apply(PointSubjectContract $subject, $point)
	{
		$user_id = $subject->user_id;

		$adjustment = new Adjustment([
			'type'        => Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT,
			'label'       => '使用积分',
			'origin_type' => 'point',
			'origin_id'   => $user_id,
		]);

		$amount = -1 * (int) round($point * settings('point_deduction_money'), 0, PHP_ROUND_HALF_DOWN);
		if ($amount != 0) {

			$adjustment->amount = $amount;

			$subject->addAdjustment($adjustment);

			return true;
		} else {
			return false;
		}
	}
}
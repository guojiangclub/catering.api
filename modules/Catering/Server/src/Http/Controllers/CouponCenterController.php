<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Server\Repositories\CouponCenterRepository;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;

class CouponCenterController extends Controller
{
	protected $couponCenterRepository;

	public function __construct(CouponCenterRepository $couponCenterRepository)
	{
		$this->couponCenterRepository = $couponCenterRepository;
	}

	public function list()
	{
		$user       = request()->user();
		$activities = $this->couponCenterRepository->getActivityList($user);
		if ($activities->count() > 0) {
			foreach ($activities as $activity) {

				foreach ($activity->items as $key => $value) {
					if (is_null($value->discount)) {
						unset($activity->items[$key]);
					} else {
						$value->discount->has_get_status = 0;
						if (settings('coupon_bg_img')) {
							$value->discount->discount_bg_img = settings('coupon_bg_img');
						}
					}
				}

				$activity->newItems = $activity->items->values();
			}
		}

		return $this->success($activities);
	}
}
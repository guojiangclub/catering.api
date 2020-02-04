<?php

namespace GuoJiangClub\Catering\Component\Gift\Processor;

use GuoJiangClub\Catering\Component\Gift\Services\DirectionalCouponService;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;
use GuoJiangClub\Catering\Component\Gift\Models\GiftCouponReceive;
use GuoJiangClub\Catering\Component\Gift\Repositories\GiftDirectionalCouponRepository;
use GuoJiangClub\Catering\Component\Gift\Models\GiftDirectionalCoupon;
use DB;

class DirectionalCouponProcessor
{
	protected $discountService;
	protected $directionalCouponService;
	protected $giftDirectionalCouponRepository;

	public function __construct(
		DirectionalCouponService $DirectionalCouponService, DiscountService $discountService, GiftDirectionalCouponRepository $giftDirectionalCouponRepository
	)
	{
		$this->directionalCouponService        = $DirectionalCouponService;
		$this->discountService                 = $discountService;
		$this->giftDirectionalCouponRepository = $giftDirectionalCouponRepository;
	}

	public function DirectionalCoupon($gift, $user_id)
	{
		try {
			if (count($user_id) > 0) {
				foreach ($user_id as $item) {
					if ($gift = GiftDirectionalCoupon::where('status', 1)->find($gift->id)) {
						$num = GiftCouponReceive::where('user_id', $item)->where('type', 'gift_directional_coupon')->where('discount_id', $gift->coupon_id)->where('type_id', $gift->id)->count();
						if (!$num) {
							DB::beginTransaction();
							if ($couponConvert = $this->discountService->getCouponConvert($gift->coupon->code, $item)) {
								GiftCouponReceive::create(['type_id' => $gift->id, 'discount_id' => $gift->coupon_id, 'user_id' => $item, 'type' => 'gift_directional_coupon']);
							}
							DB::commit();
						}
					} else {
						break;
					}
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

}
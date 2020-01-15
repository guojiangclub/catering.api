<?php

namespace GuoJiangClub\Catering\Server\Service;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountItemContract;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Component\Discount\Checkers\DiscountEligibilityChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\CouponEligibilityChecker;

class DiscountService
{
	protected $discountRepository;
	protected $discountChecker;
	protected $couponChecker;
	protected $couponRepository;

	public function __construct(DiscountRepository $discountRepository, DiscountEligibilityChecker $discountEligibilityChecker, CouponRepository $couponRepository, CouponEligibilityChecker $couponEligibilityChecker)
	{
		$this->discountRepository = $discountRepository;
		$this->discountChecker    = $discountEligibilityChecker;
		$this->couponRepository   = $couponRepository;
		$this->couponChecker      = $couponEligibilityChecker;
	}

	public function getCouponByUser(DiscountItemContract $discountItemContract, $userId, $channel = 'ec')
	{
		try {
			$coupons = $this->couponRepository->findActiveByUser($userId, false, $channel);
			if (count($coupons) == 0) {
				return [];
			}

			$filtered = $coupons->filter(function ($item) use ($discountItemContract) {
				if ($item->discount->usestart_at > Carbon::now()) {
					return false;
				} else {
					return $this->discountChecker->isEligibleItem($discountItemContract, $item->discount) AND $item->discount->is_open;
				}
			});

			if (count($filtered) == 0) {
				return [];
			}

			return $filtered->values();
		} catch (\Exception $e) {
			\Log::info('优惠券异常:' . $e->getMessage());

			return [];
		}
	}

	/**
	 * 优惠券领取
	 *
	 * @param      $couponCode
	 * @param      $user_id
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getCouponConvert($couponCode, $user_id)
	{
		$discount = $this->discountRepository->getCouponByCodeAndUserID($couponCode, $user_id);
		if (!$discount) {
			throw new \Exception('该优惠券码不存在或已过期');
		}

		if ($discount->has_get) {
			throw new \Exception('您已经领取过该优惠券');
		}

		if ($discount->has_max) {
			throw new \Exception('该优惠券已领完库存不足');
		}

		$coupon = $this->couponRepository->getCouponsByUserID($user_id, $discount->id);

		return $coupon;
	}
}

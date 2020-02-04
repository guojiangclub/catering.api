<?php

namespace GuoJiangClub\Catering\Component\Gift\Listeners\DirectionalCoupon;

use GuoJiangClub\Catering\Component\Gift\Jobs\AutoDirectionalCoupon;
use GuoJiangClub\Catering\Component\Gift\Repositories\GiftDirectionalCouponRepository;
use GuoJiangClub\Catering\Component\Gift\Services\DirectionalCouponService;

class CouponEventListener
{
	protected $GiftDirectionalCouponRepository;

	protected $DirectionalCouponService;

	public function __construct(GiftDirectionalCouponRepository $GiftDirectionalCouponRepository
		, DirectionalCouponService $DirectionalCouponService
	)
	{
		$this->GiftDirectionalCouponRepository = $GiftDirectionalCouponRepository;
		$this->DirectionalCouponService        = $DirectionalCouponService;
	}

	public function handle($id)
	{
		$gift = $this->GiftDirectionalCouponRepository->checkoutByID($id);
		if ($gift) {
			$user_id = $this->DirectionalCouponService->getUserID($gift->toArray());
			if (is_array($user_id[0])) {
				foreach ($user_id as $item) {
					dispatch(new AutoDirectionalCoupon($gift, $item));
				}
			} else {
				dispatch(new AutoDirectionalCoupon($gift, $user_id));
			}
		}
	}
}
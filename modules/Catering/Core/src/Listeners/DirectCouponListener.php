<?php

namespace GuoJiangClub\Catering\Core\Listeners;

use ElementVip\Component\Gift\Services\DirectionalCouponService;
use GuoJiangClub\Catering\Backend\Repositories\GiftDirectionalCouponRepository;
use GuoJiangClub\Catering\Core\Jobs\AutoDirectionalCoupon;

class DirectCouponListener
{
	protected $GiftDirectionalCouponRepository;
	protected $DirectionalCouponService;

	public function __construct(GiftDirectionalCouponRepository $giftDirectionalCouponRepository,
	                            DirectionalCouponService $directionalCouponService
	)
	{
		$this->GiftDirectionalCouponRepository = $giftDirectionalCouponRepository;
		$this->DirectionalCouponService        = $directionalCouponService;
	}

	public function directCoupon($id)
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

	public function subscribe($events)
	{
		$events->listen(
			'st.directional.coupon',
			'GuoJiangClub\Catering\Core\Listeners\DirectCouponListener@directCoupon'
		);
	}
}
<?php

namespace GuoJiangClub\Catering\Component\Gift\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Gift\Models\GiftCoupon;

class GiftCouponRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return GiftCoupon::class;
	}

}

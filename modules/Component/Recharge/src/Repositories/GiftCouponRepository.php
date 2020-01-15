<?php

namespace GuoJiangClub\Catering\Component\Recharge\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Recharge\Models\GiftCoupon;

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

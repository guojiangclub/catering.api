<?php

namespace GuoJiangClub\Catering\Component\Gift\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Gift\Models\GiftCouponReceive;

class GiftCouponReceiveRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return GiftCouponReceive::class;
	}

	public function getCouponsRecord($id, $type, $mobile = null, $limit = 15)
	{


		$query = $this->model
			->where('type_id', $id)
			->where('type', $type)
			->with('coupon')
			->with('user');

		if (!empty($mobile)) {
			$query = $query->whereHas('user', function ($query) use ($mobile) {
				return $query->where('mobile', 'like', '%' . $mobile . '%');
			});
		}

		return $query->OrderBy('created_at', 'desc')->paginate($limit);
	}

}

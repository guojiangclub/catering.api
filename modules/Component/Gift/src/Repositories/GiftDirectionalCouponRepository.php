<?php

namespace GuoJiangClub\Catering\Component\Gift\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Gift\Models\GiftDirectionalCoupon;

class GiftDirectionalCouponRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return GiftDirectionalCoupon::class;
	}

	public function getAll($name = null, $status = 1, $limit = 15)
	{

		$query = $this->model
			->where('status', $status);

		if (!empty($name)) {
			$query = $query->where('name', 'like', "%$name%");
		}

		return $query->with('coupon')->OrderBy('created_at', 'desc')->paginate($limit);
	}

	public function checkoutByID($id)
	{
		return $this->model->where('status', 1)->with('coupon')->find($id);
	}

}

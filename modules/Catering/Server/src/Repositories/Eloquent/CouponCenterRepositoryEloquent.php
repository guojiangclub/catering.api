<?php

namespace GuoJiangClub\Catering\Server\Repositories\Eloquent;

use Carbon\Carbon;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\CouponCenter;
use GuoJiangClub\Catering\Server\Repositories\CouponCenterRepository;
use Prettus\Repository\Eloquent\BaseRepository;

class CouponCenterRepositoryEloquent extends BaseRepository implements CouponCenterRepository
{
	public function model()
	{
		return CouponCenter::class;
	}

	public function getActivityList($user)
	{
		$coupons      = Coupon::where('user_id', $user->id)->get();
		$discount_ids = [];
		if ($coupons->count() > 0) {
			$discount_ids = $coupons->pluck('discount_id')->all();
		}

		$where = ['status' => 1, 'starts_at' => ['starts_at', '<=', Carbon::now()], 'ends_at' => ['ends_at', '>', Carbon::now()]];
		$query = $this->scopeQuery(function ($query) use ($where, $discount_ids) {
			if (!empty($where)) {
				foreach ($where as $key => $value) {
					if (is_array($value)) {
						list($key, $condition, $val) = $value;
						$query = $query->where($key, $condition, $val);
					} else {
						$query = $query->where($key, $value);
					}
				}
			}

			$query = $query->with(['items.discount' => function ($query) use ($discount_ids) {
				if (!empty($discount_ids)) {
					$query->whereNotIn('id', $discount_ids);
				}

				return $query;
			}]);

			return $query->orderBy('created_at', 'DESC');
		});

		return $query->get();
	}
}
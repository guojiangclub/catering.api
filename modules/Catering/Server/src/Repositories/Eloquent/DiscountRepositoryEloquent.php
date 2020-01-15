<?php

namespace GuoJiangClub\Catering\Server\Repositories\Eloquent;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent\DiscountRepositoryEloquent as BaseDiscountRepositoryEloquent;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;

class DiscountRepositoryEloquent extends BaseDiscountRepositoryEloquent implements DiscountRepository
{
	public function model()
	{
		return Discount::class;
	}

	public function getCouponByCodeAndUserID($coupon_code, $user_id)
	{

		$query = $this->model->where('status', 1)->where('coupon_based', 1)->where('code', $coupon_code);

		$data = $query
			->where(function ($query) {
				$query->whereNull('starts_at')
					->orWhere(function ($query) {
						$query->where('starts_at', '<', Carbon::now());
					});
			})
			->where(function ($query) {
				$query->whereNull('ends_at')
					->orWhere(function ($query) {
						$query->where('ends_at', '>', Carbon::now());
					});
			})
			->first();

		if (!$data) {
			return null;
		}

		$coupon_data = Coupon::where('discount_id', $data->id);

		$coupon = $coupon_data->where('user_id', $user_id)->count();

		$coupons = $coupon_data->count();

		if ($coupon >= $data->per_usage_limit) {

			$data->has_get = true;
		}

		if ($data->usage_limit <= 0 || $coupons >= $data->usage_limit) {

			$data->has_max = true;
		}

		return $data;
	}
}
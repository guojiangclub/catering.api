<?php

namespace GuoJiangClub\Catering\Backend\Repositories;

use Carbon\Carbon;
use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class DiscountRepository extends BaseRepository
{
	public function model()
	{
		return Discount::class;
	}

	public function boot()
	{
		$this->pushCriteria(app(RequestCriteria::class));
	}

	public function getCouponList($where)
	{
		return $this->scopeQuery(function ($query) use ($where) {
			$query = $query->Where(function ($query) use ($where) {
				if (is_array($where)) {
					foreach ($where as $key => $value) {
						if (is_array($value)) {
							list($operate, $va) = $value;
							$query = $query->where($key, $operate, $va);
						} else {
							$query = $query->where($key, $value);
						}
					}
				}
			});

			return $query->orderBy('created_at', 'desc');
		})->paginate(15);
	}

	/**
	 * 获取促销活动、优惠券列表数据
	 *
	 * @param $where
	 * @param $orWhere
	 *
	 * @return mixed
	 */
	public function getDiscountList($where, $orWhere, $limit = 0)
	{
		$query = $this->scopeQuery(function ($query) use ($where, $orWhere) {
			$query = $query->Where(function ($query) use ($where) {
				if (is_array($where)) {
					foreach ($where as $key => $value) {
						if (is_array($value)) {
							list($operate, $va) = $value;
							$query = $query->where($key, $operate, $va);
						} else {
							$query = $query->where($key, $value);
						}
					}
				}
			});

			if (count($orWhere)) {
				$query = $query->orWhere(function ($query) use ($orWhere) {
					if (is_array($orWhere)) {
						foreach ($orWhere as $key => $value) {
							if (is_array($value)) {
								list($operate, $va) = $value;
								$query = $query->where($key, $operate, $va);
							} else {
								$query = $query->where($key, $value);
							}
						}
					}
				});
			}

			return $query->with('actions')->orderBy('created_at', 'desc');
		});
		if ($limit) {
			return $query->paginate($limit);
		}

		return $query->all();
	}

	/**
	 * get active discount list
	 *
	 * @param int $isCoupon 0:discount 1:coupon 2:all
	 *
	 * @return mixed
	 */
	public function findActive($isCoupon = 0, $channel = 'ec')
	{
		$query = $this->model->where('status', 1)->where('channel', $channel);

		if ($isCoupon != 2) {
			$query = $query->where('coupon_based', $isCoupon);
		}

		return $query
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
			})->get();
	}
}

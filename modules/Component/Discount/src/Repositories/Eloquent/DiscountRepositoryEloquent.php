<?php

namespace GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use GuoJiangClub\Catering\Component\Discount\Repositories\DiscountRepository;
use Prettus\Repository\Eloquent\BaseRepository;

class DiscountRepositoryEloquent extends BaseRepository implements DiscountRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Discount::class;
	}

	/**
	 * get active discount
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
			})->with('rules', 'actions')->get();
	}

	/**
	 * get discount by code
	 *
	 * @param bool $isCoupon
	 *
	 * @return mixed
	 */
	public function getDiscountByCode($code, $isCoupon = false)
	{
		if (empty($code)) {
			return false;
		}

		return $this->model->where('status', 1)->where('coupon_based', $isCoupon)
			->where('code', $code)
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
			})->with('rules', 'actions')->get()->first();
	}

	/**
	 * 根据 discount code and type 获取促销或者优惠券
	 *
	 * @param      $code
	 * @param bool $isCoupon
	 * @param null $type
	 *
	 * @return mixed
	 */
	public function getDiscountsByCodeAndType($code, $isCoupon = false, $type = null)
	{
		if (empty($code)) {
			return false;
		}

		$query = $this->model->where('status', 1)->where('coupon_based', $isCoupon);

		if (is_array($code)) {
			$query->whereIn('code', $code);
		} else {
			$query->where('code', $code);
		}

		$query = $query
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
			})->with('rules', 'actions');

		if ($type) {
			$query = $query->where('type', $type);
		}

		return $query->get();
	}

	/**
	 * 根据actiontype 获取discount，因为现在需要根据积分action获取 discounts
	 *
	 * @param $actionType
	 *
	 * @return mixed
	 */
	public function getDiscountsByActionType($actionType)
	{
		if (empty($actionType)) {
			return false;
		}

		$query = $this->model->where('status', 1);

		$query = $query
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
			->whereHas('actions', function ($query) use ($actionType) {
				$query->where('type', $actionType);
			})
			->with('rules', 'actions');

		return $query->get();
	}

	/**
	 * 获取可领取优惠券列表/促销优惠活动
	 *
	 * @param int $is_coupon
	 * @param int $channel
	 * @param int $limit
	 *
	 * @return mixed
	 */
	public function getDiscountByType($is_coupon = 1, $channel = 'ec', $limit = 10, $is_agent_share = 0)
	{
		$query = $this->model->where('status', 1)->where('coupon_based', $is_coupon)->where('channel', $channel);

		if ($is_agent_share) {
			$query = $query->where('is_agent_share', $is_agent_share);
		} else {
			$query = $query->where('is_open', 1);
		}

		$query = $query
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
			->with('rules', 'actions');

		return $query->paginate($limit);
	}

	public function getCouponsList($user_id = 0)
	{
		$query = $this->model->where('status', 1)->where('coupon_based', 1)->where('is_open', 1);

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
			->with('rules', 'actions')->get();
		if (count($data) > 0 AND $user_id) {
			foreach ($data as $item) {
				$coupons = Coupon::where('discount_id', $item->id)->where('user_id', $user_id)->get();
				if (count($coupons) >= $item->per_usage_limit) {
					$item->has_get = true;
				}
			}
		}

		return $data;
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

	/**
	 * get active discount by discount ids
	 *
	 * @param        $discount_ids
	 * @param int    $isCoupon
	 * @param string $channel
	 *
	 * @return mixed
	 */
	public function getDiscountByIds($discount_ids, $isCoupon = 0, $channel = 'ec')
	{
		$query = $this->model->where('status', 1)->where('channel', $channel)->whereIn('id', $discount_ids);

		if ($isCoupon != 2) {
			$query = $query->where('coupon_based', $isCoupon);
		}

		return $query->where(function ($query) {
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
			})->with('rules', 'actions')->get();
	}

}
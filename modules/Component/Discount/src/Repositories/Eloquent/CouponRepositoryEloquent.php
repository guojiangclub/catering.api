<?php

namespace GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use GuoJiangClub\Catering\Component\Discount\Repositories\CouponRepository;
use Prettus\Repository\Eloquent\BaseRepository;

class CouponRepositoryEloquent extends BaseRepository implements CouponRepository
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Coupon::class;
	}

	/**
	 * get active discount
	 *
	 * @return mixed
	 */
	public function findActiveByUser($userId, $paginate = 15, $channel = 'ec')
	{
		$res = $this->model->where('user_id', $userId)->where('channel', $channel)->whereNull('used_at')
			->where(function ($query) {
				$query->whereNull('expires_at')
					->orWhere(function ($query) {
						$query->where('expires_at', '>', Carbon::now());
					});
			})
			->with('discount', 'discount.rules', 'discount.actions')
			->whereHas('discount', function ($query) {
				$query->where(function ($query) {
					$query->whereNull('useend_at')
						->orWhere(function ($query) {
							$query->where('useend_at', '>', Carbon::now());
						});
				})->where('status', 1);
			});

		if (!$paginate) {
			return $res->get();
		}

		return $res->paginate($paginate);
	}

	public function findInvalidByUser($userId, $paginate = 15)
	{

		return $this->model->where('user_id', $userId)
			->with('discount', 'discount.rules', 'discount.actions')
			->where(function ($query) {
				$query->where(function ($query) {
					$query->whereNotNull('expires_at')->where('expires_at', '<=', Carbon::now());
				})
					->orWhere(function ($query) {
						$query->whereHas('discount', function ($query) {
							$query->where('status', 0)->orWhere('useend_at', '<=', Carbon::now());
						});
					});
			})
			->orWhere(function ($query) use ($userId) {
				$query->where('user_id', $userId)->whereNotNull('used_at');
			})
			->paginate($paginate);
	}

	/**
	 * 用户领取优惠券
	 *
	 * @param     $user_id
	 * @param     $coupon_id
	 * @param int $type
	 *
	 * @return bool|mixed
	 */
	public function getCouponsByUser($user_id, $coupon_id, $type, $utmCampaign = null, $utmSource = null)
	{
		$discount = Discount::find($coupon_id);

		//判断是否可以领取
		if ($discount->usage_limit - 1 >= 0) {
			if ($type == 1) {
				if ($discount->code == 'unstoppable_consumer') {
					if (Coupon::where('user_id', $user_id)->where('discount_id', $coupon_id)->first()) {
						return false;
					} else {
						return $coupon = $this->userGetCoupon($user_id, $coupon_id, $type, $utmCampaign, $utmSource);
					}
				} elseif ($offlineCouponCount = Coupon::where('user_id', $user_id)->where('discount_id', $coupon_id)->count() < 1) {
					$coupon = false;
					for ($i = $discount->per_usage_limit; $i > $offlineCouponCount, $i--;) {
						$coupon = $this->userGetCoupons($user_id, $coupon_id, $type, $utmCampaign, $utmSource);
					}

					return $coupon;
				} else {
					return false;
				}
			} else {
				return $this->userGetCoupons($user_id, $coupon_id, $type, $utmCampaign, $utmSource);
			}
		} else {
			return false;
		}
	}

	public function userGetCoupon($user_id, $coupon_id, $type = 0, $utmCampaign = null, $utmSource = null)
	{
		$input['user_id']     = $user_id;
		$input['discount_id'] = $coupon_id;
		if ($type == 1) {

			$array = [
				['75413', '75455']
				, ['75457', '75530']
				, ['75553', '75600']
				, ['75603', '75654']
				, ['75679', '75711']
				, ['75760', '75811']
				, ['75833', '75876']
				, ['75878', '75961']
				, ['75963', '76001'],
			];

			$randArray = $array[array_rand($array)];

			$coupon_code = createConsumerOfflineCouponCode('91634', $randArray[0], $randArray[1]);
			$coupon_list = Coupon::where(['code' => $coupon_code])->first();
			if (count($coupon_list)) {
				$coupon_code = createConsumerOfflineCouponCode('91634', $randArray[0], $randArray[1]);
			}
			$input['code']       = $coupon_code;
			$input['expires_at'] = Carbon::create(2018, 9, 24, 23, 59, 59);
		} else {
			$input['code'] = build_order_no('C');
		}
		if ($utmCampaign) {
			$input['utm_campaign'] = $utmCampaign;
		}
		if ($utmSource) {
			$input['utm_source'] = $utmSource;
		}
		$coupon = $this->create($input);
		if ($coupon) {
			$decrementDiscount = Discount::where(['id' => $coupon_id])->decrement('usage_limit');
			$incrementDisount  = Discount::where(['id' => $coupon_id])->increment('used');
			if ($decrementDiscount && $incrementDisount) {
				return $coupon;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 用户获取优惠券
	 *
	 * @param $user_id
	 * @param $coupon_id
	 *
	 * @return bool
	 */
	public function userGetCoupons($user_id, $coupon_id, $type = 0, $utmCampaign = null, $utmSource = null)
	{
		//$coupon = new Coupon();
		$discount = Discount::find($coupon_id);
		if ($discount->channel == 'shop') {
			$input['channel'] = 'shop';
		}

		$input['user_id']     = $user_id;
		$input['discount_id'] = $coupon_id;

		if ($discount->useend_at) {
			$input['expires_at'] = $discount->useend_at;
		} else {
			$input['expires_at'] = Carbon::now()->addMonth(6);
		}

		if ($type == 1) {
			$coupon_code = createOfflineCouponCode();
			$coupon_list = Coupon::where(['code' => $coupon_code])->first();
			if (count($coupon_list)) {
				$coupon_code = createOfflineCouponCode();
			}
			$input['code'] = $coupon_code;
			/*$input['expires_at'] = Carbon::now()->addMonth(6);*/
		} else {
			$input['code'] = build_order_no('C');
		}
		if ($utmCampaign) {
			$input['utm_campaign'] = $utmCampaign;
		}
		if ($utmSource) {
			$input['utm_source'] = $utmSource;
		}
		$coupon = $this->create($input);
		if ($coupon) {
			$decrementDiscount = Discount::where(['id' => $coupon_id])->decrement('usage_limit');
			$incrementDisount  = Discount::where(['id' => $coupon_id])->increment('used');
			if ($decrementDiscount && $incrementDisount) {
				return $coupon;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 2018
	 * 新用户领取优惠券
	 */
	public function getCouponsByUserID($user_id, $coupon_id, $utmCampaign = null, $utmSource = null)
	{
		$discount = Discount::find($coupon_id);

		if (!isset($discount->usage_limit) || $discount->usage_limit < 1) {
			return false;
		}

		$input['channel']     = $discount->channel;
		$input['user_id']     = $user_id;
		$input['discount_id'] = $coupon_id;

		if ($discount->useend_at) {
			$input['expires_at'] = $discount->useend_at;
		} else {
			$input['expires_at'] = $discount->ends_at;
		}

		$input['code'] = build_order_no('C');

		if ($utmCampaign) {
			$input['utm_campaign'] = $utmCampaign;
		}

		if ($utmSource) {
			$input['utm_source'] = $utmSource;
		}

		$coupon = $this->create($input);

		if ($coupon) {

			$decrementDiscount = Discount::where(['id' => $coupon_id])->decrement('usage_limit');

			$incrementDisount = Discount::where(['id' => $coupon_id])->increment('used');

			if ($decrementDiscount AND $incrementDisount) {

				return $coupon;
			}
		}

		return false;
	}

	public function getCouponDetails($couponId, $user_id)
	{
		return $this->model->where(['user_id' => $user_id, 'id' => $couponId])->with('discount', 'discount.rules', 'discount.actions')->first();
	}

	public function getValidCouponCountByUser($userId)
	{
		return $this->model->where('user_id', $userId)->whereNull('used_at')
			->where(function ($query) {
				$query->whereNull('expires_at')
					->orWhere(function ($query) {
						$query->where('expires_at', '>', Carbon::now());
					});
			})
			->with('discount', 'discount.rules', 'discount.actions')
			->whereHas('discount', function ($query) {
				$query
					->whereNull('ends_at')
					->orWhere(function ($query) {
						$query->where('ends_at', '>', Carbon::now());
					})
					->where('status', 1);
//                    ->where('type', 0);
			})->count();
	}

	/**
	 * 获取当前用户已有该优惠券的数量
	 *
	 * @param $discountId
	 * @param $userId
	 *
	 * @return mixed
	 */
	public function getCouponCountByUser($discountId, $userId, $utmCampaign = null, $utmSource = null)
	{
		if ($utmCampaign) {
			$query = $this->model->where('user_id', $userId)->where('utm_campaign', $utmCampaign)->where('discount_id', $discountId);
			if ($utmSource) {
				$query = $query->where('utm_source', $utmSource);
			}

			return $query->count();
		} else {
			return $this->model->where('user_id', $userId)->where('discount_id', $discountId)->count();
		}
	}

	public function getUserCouponsByUtm($userId, $utmCampaign, $utmSource)
	{
		return $this->model->where('user_id', $userId)->where('utm_campaign', $utmCampaign)
			->where('utm_source', $utmSource)->with('discount')->get();
	}

	/**
	 * 获取已使用的优惠券
	 *
	 * @param     $userId
	 * @param int $paginate
	 *
	 * @return mixed
	 */
	public function findUsedByUser($userId, $paginate = 15)
	{
		return $this->model->where('user_id', $userId)->whereNotNull('used_at')
			->with('discount', 'discount.rules', 'discount.actions')
			->paginate($paginate);
	}

	/**
	 * 根据暗码获取优惠券
	 *
	 * @param $code
	 *
	 * @return mixed
	 */
	public function findCouponBySecretCode($code)
	{
		return $this->model->where('code', $code)->whereNull('used_at')
			->with('discount', 'discount.rules', 'discount.actions')
			->where(function ($query) {
				$query->whereNull('expires_at')
					->orWhere(function ($query) {
						$query->where('expires_at', '>', Carbon::now());
					});
			})->whereHas('discount', function ($query) {
				$query->where(function ($query) {
					$query->whereNull('ends_at')
						->orWhere(function ($query) {
							$query->where('ends_at', '>', Carbon::now());
						});
				})->where('status', 1);
			})->get();
	}
}
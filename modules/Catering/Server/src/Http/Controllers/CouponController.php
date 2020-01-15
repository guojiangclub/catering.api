<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Server\Service\DiscountService;
use GuoJiangClub\Catering\Server\Transformers\CouponsTransformer;
use GuoJiangClub\Catering\Server\Transformers\DiscountTransformer;
use Illuminate\Http\Request;
use QrCode;
use Storage;

class CouponController extends Controller
{
	protected $discountRepository;
	protected $couponRepository;
	protected $discountService;

	public function __construct(DiscountRepository $discountRepository, CouponRepository $couponRepository, DiscountService $discountService)
	{
		$this->discountRepository = $discountRepository;
		$this->couponRepository   = $couponRepository;
		$this->discountService    = $discountService;
	}

	/**
	 * 获取可用的优惠券列表
	 *
	 * @param $id
	 *
	 * @return \Dingo\Api\Http\Response|mixed
	 */
	public function index(Request $request)
	{
		$user = request()->user();
		$gift = $request->input('codes');
		if (!empty($gift)) {
			$discounts       = $this->discountRepository->findActive(1);
			$discounts_codes = $discounts->pluck('code')->all();
			$filter          = collect($gift)->filter(function ($item) use ($discounts_codes) {
				return in_array($item, $discounts_codes);
			});

			if (count($filter) > 0) {
				foreach ($filter->all() as $value) {
					$discount = $this->discountRepository->getCouponByCodeAndUserID($value, $user->id);
					if (!$discount) {
						continue;
					}

					if ($discount->has_get) {
						continue;
					}

					if ($discount->has_max) {
						continue;
					}

					$coupon = $this->couponRepository->getCouponsByUserID($user->id, $discount->id);
					if ($coupon) {
						$qrCodeSavePath = 'user/coupon/' . $user->id . '_' . $coupon->code . '.png';
						if (!Storage::disk('public')->exists($qrCodeSavePath)) {
							$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($coupon->code);
							Storage::disk('public')->put($qrCodeSavePath, $res);
						}

						$coupon->coupon_use_code = Storage::disk('public')->url($qrCodeSavePath);
						$coupon->save();
					}
				}
			}
		}

		$coupons = $this->couponRepository->findActiveByUser($user->id, false);

		return $this->success(['coupons' => $coupons]);
	}

	/**
	 * 我的优惠券列表
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function list()
	{
		$user = request()->user();

		$limit = request('limit') ?: 15;
		$type  = request('type') ?: 'active';

		if ($type == 'active') {
			$coupons = $this->couponRepository->findActiveByUser($user->id, $limit);
		} else {
			$coupons = $this->couponRepository->findInvalidByUser($user->id, $limit);
		}

		return $this->response()->paginator($coupons, new CouponsTransformer());
	}

	/**
	 * 领取优惠券
	 *
	 * @return \Dingo\Api\Dispatcher|\Dingo\Api\Http\Response|mixed
	 */
	public function getCouponConvert()
	{
		if (!request('coupon_code')) {
			return $this->failed('请输入兑换码');
		}

		try {
			$coupon_code = request('coupon_code');
			$user        = request()->user();

			$couponConvert = $this->discountService->getCouponConvert($coupon_code, $user->id);
			if ($couponConvert) {
				$qrCodeSavePath = 'user/coupon/' . $user->id . '_' . $couponConvert->code . '.png';
				if (!Storage::disk('public')->exists($qrCodeSavePath)) {
					$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($couponConvert->code);
					Storage::disk('public')->put($qrCodeSavePath, $res);
				}

				$couponConvert->coupon_use_code = Storage::disk('public')->url($qrCodeSavePath);
				$couponConvert->save();
			}

			$couponConvert = $couponConvert ? $couponConvert->toArray() : [];

			return $this->success($couponConvert);
		} catch (\Exception $exception) {
			return $this->failed($exception->getMessage());
		}
	}

	/**
	 * 获取优惠券详情
	 *
	 * @param $id
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function getDiscountDetail($id)
	{
		$discount = $this->discountRepository->find($id);

		unset($discount->code);

		return $this->response()->item($discount, new DiscountTransformer());
	}

	/**
	 * 新人礼 11-28至11-30
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function gift()
	{
		$new_member_coupon_starts_at = settings('new_member_coupon_starts_at');
		$new_member_coupon_ends_at   = settings('new_member_coupon_ends_at');
		if (!$new_member_coupon_starts_at || !$new_member_coupon_ends_at) {
			return $this->success(['dialog' => false]);
		}

		$time_start = strtotime($new_member_coupon_starts_at);
		$end_start  = strtotime($new_member_coupon_ends_at);
		if (time() > $end_start || time() < $time_start) {
			return $this->success(['dialog' => false]);
		}

		$user = auth('api')->user();
		if (!$user) {
			return $this->success(['dialog' => true]);
		}

		$coupons = settings('new_member_coupon_ids');
		if (!$coupons) {
			return $this->success(['dialog' => false]);
		}

		$coupons = explode(',', $coupons);
		$hasGet  = Coupon::where('user_id', $user->id)->whereIn('discount_id', $coupons)->first();
		if ($hasGet) {
			return $this->success(['dialog' => false]);
		}

		$discount = Discount::where('status', 1)->where('coupon_based', 1)->whereIn('id', $coupons)->get();
		if (count($discount) != count($coupons)) {
			return $this->success(['dialog' => false]);
		}

		$rand     = $coupons[array_rand($coupons)];
		$discount = $this->discountRepository->findWhere(['id' => $rand])->first();
		if (!$discount) {
			return $this->success(['dialog' => false]);
		}

		try {
			$couponConvert = $this->discountService->getCouponConvert($discount->code, $user->id);

			$couponConvert = $couponConvert ? $couponConvert->toArray() : [];

			return $this->success(['coupon' => $couponConvert, 'dialog' => true]);
		} catch (\Exception $exception) {
			\Log::info($exception->getTraceAsString());

			return $this->success(['dialog' => false, 'message' => $exception->getMessage()]);
		}
	}
}

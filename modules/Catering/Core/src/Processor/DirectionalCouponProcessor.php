<?php

namespace GuoJiangClub\Catering\Core\Processor;

use GuoJiangClub\Catering\Core\Auth\User;
use GuoJiangClub\Catering\Core\Models\GiftCouponReceive;
use GuoJiangClub\Catering\Core\Models\GiftDirectionalCoupon;
use GuoJiangClub\Catering\Server\Service\DiscountService;
use DB;
use QrCode;

class DirectionalCouponProcessor
{
	protected $discountService;

	public function __construct(DiscountService $discountService)
	{
		$this->discountService = $discountService;
	}

	public function DirectionalCoupon($gift, $user_id)
	{

		if (count($user_id) > 0) {
			foreach ($user_id as $item) {
				try {
					DB::beginTransaction();
					
					if ($gift = GiftDirectionalCoupon::where('status', 1)->find($gift->id)) {
						$num = GiftCouponReceive::where('user_id', $item)->where('origin_type', 'gift_directional_coupon')->where('discount_id', $gift->coupon_id)->where('origin_id', $gift->id)->count();
						if (!$num) {
							if ($couponConvert = $this->discountService->getCouponConvert($gift->coupon->code, $item)) {
								GiftCouponReceive::create([
									'origin_id'   => $gift->id,
									'discount_id' => $gift->coupon_id,
									'user_id'     => $item,
									'origin_type' => 'gift_directional_coupon',
									'coupon_id'   => $couponConvert->id]);

								$qrCodeSavePath = 'user/coupon/' . $item . '_' . $couponConvert->code . '.png';
								if (!\Storage::disk('public')->exists($qrCodeSavePath)) {
									$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($couponConvert->code);
									\Storage::disk('public')->put($qrCodeSavePath, $res);
								}

								$couponConvert->coupon_use_code = \Storage::disk('public')->url($qrCodeSavePath);
								$couponConvert->save();

								$user = User::find($item);
								event('st.wechat.message.coupon', [$user, $couponConvert]);
							}
						}
					} else {
						break;
					}

					DB::commit();
				} catch (\Exception $exception) {
					DB::rollBack();
					\Log::info($exception->getTraceAsString());
					\Log::info($exception->getMessage());
				}
			}
		}
	}

}
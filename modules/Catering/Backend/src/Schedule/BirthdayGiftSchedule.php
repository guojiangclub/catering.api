<?php

namespace GuoJiangClub\Catering\Backend\Schedule;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Scheduling\Schedule\Scheduling;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Backend\Models\GiftActivity;
use GuoJiangClub\Catering\Core\Models\GiftCouponReceive;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Server\Service\DiscountService;
use QrCode;
use DB;

class BirthdayGiftSchedule extends Scheduling
{
	public function schedule()
	{
		$this->schedule->call(function () {
			\Log::info('进入生日礼定时任务');
			$birthday = $this->giftBirthday();

			if ($birthday) {
				$user = DB::select("SELECT * FROM el_user WHERE PERIOD_DIFF(DATE_FORMAT(CURDATE(),'%m'),DATE_FORMAT(birthday,'%m'))=-1");

				if (count($user) > 0) {
					$this->userGiftBirthdayPoint($user, $birthday);
				}
			}
		})->monthlyOn(28, "10:00");
	}

	private function giftBirthday()
	{
		return GiftActivity::where('type', 'gift_birthday')->where('status', 1)
			->where('ends_at', '>=', Carbon::now())
			->where('starts_at', '<=', Carbon::now())
			->first();
	}

	private function userGiftBirthdayPoint($user, $activity)
	{
		try {
			$getCoupon = false;
			if ($activity->gift->count()) {
				$discount_ids = $activity->gift->pluck('discount_id')->toArray();

				$discounts = app(DiscountRepository::class)->getDiscountByIds($discount_ids, 1);
				if (!$discounts->count()) {
					$getCoupon = false;
				} else {
					$getCoupon = true;
				}
			}

			$time     = Carbon::now()->timestamp;
			$birthday = date('Y-m-d', $time);

			foreach ($user as $item) {
				if ($item->birthday) {
					if ($activity->point) {   //发送生日积分
						$point_status = false;

						$point = Point::where('action', 'gift_birthday_point')
							->where('user_id', $item->user_id)
							->orderBy('created_at', 'desc')
							->first();
						if ($point) {
							if (intval(strtotime(date('Y-m-d', strtotime($point->created_at))) !== intval(strtotime($birthday)))) {
								$point_status = true;
							}
						}

						if ($point == null || $point_status) {
							Point::create([
								'user_id'   => $item->id,
								'action'    => 'gift_birthday_point',
								'note'      => date('Y', $time) . '年生日礼赠送积分',
								'item_type' => GiftActivity::class,
								'item_id'   => $activity->id,
								'value'     => $activity->point]);

							event('point.change', [$item->id]);

							//TODO::发送积分变动模板消息
							event('st.wechat.message.point', [$user, '生日礼赠送积分', $activity->point]);
						}
					}

					//赠送优惠券
					if ($getCoupon) {

						$coupon_status = true;
						$checkCoupon   = GiftCouponReceive::where('user_id', $item->id)
							->where('origin_type', 'gift_birthday')
							->where('origin_id', $activity->id)
							->orderBy('created_at', 'desc')
							->first();

						if ($checkCoupon) {
							if (intval(strtotime(date('Y-m-d', strtotime($checkCoupon->created_at))) == intval(strtotime($birthday)))) {
								$coupon_status = false;
							}
						}

						if (!$coupon_status) {
							continue;
						}

						if ($activity->type == GiftActivity::TYPE_RANDOM) {
							$discounts = $discounts->random(1);
						}

						foreach ($discounts as $discount) {
							$couponConvert = app(DiscountService::class)->getCouponConvert($discount->code, $item->id);
							if ($couponConvert) {
								$qrCodeSavePath = 'user/coupon/' . $item->id . '_' . $couponConvert->code . '.png';
								if (!\Storage::disk('public')->exists($qrCodeSavePath)) {
									$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($couponConvert->code);
									\Storage::disk('public')->put($qrCodeSavePath, $res);
								}

								$couponConvert->coupon_use_code = \Storage::disk('public')->url($qrCodeSavePath);

								$date                      = date("Y-m-d", strtotime("last day of +1 month", strtotime(Carbon::now())));
								$couponConvert->expires_at = $date . " 23:59:59";
								$couponConvert->save();
							}
							event('st.wechat.message.coupon', [$user, $couponConvert]);
						}
					}
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}
}
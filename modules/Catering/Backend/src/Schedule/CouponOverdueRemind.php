<?php

namespace GuoJiangClub\Catering\Backend\Schedule;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Scheduling\Schedule\Scheduling;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Core\Auth\User;
use GuoJiangClub\Catering\Core\Notifications\OverdueRemind;

class CouponOverdueRemind extends Scheduling
{
	public function schedule()
	{
		$this->schedule->call(function () {
			\Log::info('进入优惠券过期提醒定时任务');
			$coupons = Coupon::whereNull('used_at')->where(function ($query) {
				$query->whereNull('expires_at')->orWhere(function ($query) {
					$query->where('expires_at', '>', Carbon::now());
				});
			})->with('discount')->whereHas('discount', function ($query) {
				$query->where('status', 1)->where(function ($query) {
					$query->whereNull('useend_at')->orWhere(function ($query) {
						$query->where('useend_at', '>', Carbon::now());
					});
				});
			})->get();

			if ($coupons->count() > 0) {
				foreach ($coupons as $coupon) {
					if (strtotime('-3 day', strtotime($coupon->expires_at)) > time()) {
						continue;
					}

					$user = User::where('status', 1)->where('id', $coupon->user_id)->first();
					if ($user) {
						$user->notify(new OverdueRemind(['coupon' => $coupon]));
					}
				}
			}
		})->dailyAt('11:00');
	}
}